<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesOrderRequest;
use App\Http\Resources\SalesOrderResource;
use App\Models\AccountLedgerName;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetails;
use App\Services\AccountsCreditService;
use App\Services\AccountsDebitService;
use App\Services\InvoiceWisePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DailyPrice;
use App\Services\EpLedgerService;
use App\Services\EggStockService;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
class SalesOrderController extends Controller
{
    private $accountDebit;
    private $accountCredit;
    protected $ledgerService;
    protected $cacheService;
    protected $eggStockService;
    protected $invoicePayment;
    public function __construct(AccountsDebitService $accountDebit, AccountsCreditService $accountCredit, EpLedgerService $ledgerService, EggStockService $eggStockService, CacheService $cacheService, InvoiceWisePaymentService $invoicePayment)
    {
        $this->accountDebit = $accountDebit;
        $this->accountCredit = $accountCredit;
        $this->ledgerService = $ledgerService;
        $this->eggStockService = $eggStockService;
        $this->cacheService = $cacheService;
        $this->invoicePayment = $invoicePayment;
    }
    public function indexOLD(Request $request)
    {

        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

        $saleId = $request->saleId ?? null;
        $dealerId = $request->dealerId ?? null;
        $bookingId = $request->bookingId ?? null;
        $salesPointId = $request->salesPointId ?? null;
        $salesPerson = $request->salesPerson ?? null;
        $productId = $request->productId ?? null;
        $childCategoryId = $request->childCategoryId ?? null;

        $startDate = $request->input('startDate', $oneYearAgo);
        $endDate = $request->input('endDate', $today);

        $status = $request->status ?? null;

        $query = SalesOrder::query();

        // Filter by saleId
        if ($saleId) {
            $query->where('saleId', 'LIKE', '%' . $saleId . '%');
        }
        // Filter by dealerId
        if ($dealerId) {
            $query->orWhere('dealerId', $dealerId);
        }

        // Filter by bookingId
        if ($bookingId) {
            $query->orWhere('bookingId', $bookingId);
        }
        // Filter by salesPointId
        if ($salesPointId) {
            $query->orWhere('salesPointId', $salesPointId);
        }
        // Filter by salesPerson
        if ($salesPerson) {
            $query->orWhere('salesPerson', operator: $salesPerson);
        }

        // Filter by productId within salesOrderDetails
        if ($productId) {
            $query->whereHas('salesDetails', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }
        // Filter by childCategoryId within salesOrderDetails' products
        if ($childCategoryId) {
            $query->whereHas('salesDetails.product', function ($q) use ($childCategoryId) {
                $q->where('childCategoryId', $childCategoryId);
            });
        }
        //Filter Date
        if ($startDate && $endDate) {
            $query->whereBetween('invoiceDate', [$startDate, $endDate]);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Fetch sales order with eager loading of related data
        //$sales_orders = $query->latest()->get();
        $sales_orders = $query->with(['dealer', 'sector', 'salesDetails.product.childCategory'])->latest()->get();


        // Check if any sales bookings found
        if ($sales_orders->isEmpty()) {
            return response()->json(['message' => 'No Sales Order found', 'data' => []], 200);
        }

        // Use the SalesOrderResource to transform the data
        $transformedSalesOrders = SalesOrderResource::collection($sales_orders);

        // Return DailyPrices transformed with the resource
        return response()->json([
            'message' => 'Success!',
            'data' => $transformedSalesOrders
        ], 200);
    }
    public function index1(Request $request)
    {
        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

        // Extract filters
        $saleId         = $request->saleId;
        $dealerId       = $request->dealerId;
        $bookingId      = $request->bookingId;
        $salesPointId   = $request->salesPointId;
        $salesPerson    = $request->salesPerson;
        $productId      = $request->productId;
        $childCategoryId = $request->childCategoryId;
        $startDate      = $request->input('startDate', $oneYearAgo);
        $endDate        = $request->input('endDate', $today);
        $status         = $request->status;

        $query = SalesOrder::query();

        // Apply filters (use `where` for accurate filtering)
        if ($saleId) {
            $query->where('saleId', 'LIKE', '%' . $saleId . '%');
        }
        if ($dealerId) {
            $query->where('dealerId', $dealerId);
        }
        if ($bookingId) {
            $query->where('bookingId', $bookingId);
        }
        if ($salesPointId) {
            $query->where('salesPointId', $salesPointId);
        }
        if ($salesPerson) {
            $query->where('salesPerson', $salesPerson);
        }
        if ($productId) {
            $query->whereHas('salesDetails', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }
        if ($childCategoryId) {
            $query->whereHas('salesDetails.product', function ($q) use ($childCategoryId) {
                $q->where('childCategoryId', $childCategoryId);
            });
        }
        if ($startDate && $endDate) {
            $query->whereBetween('invoiceDate', [$startDate, $endDate]);
        }
        if ($status) {
            $query->where('status', $status);
        }

        // Load related models and paginate
        $sales_orders = $query->with([
            'dealer:id,tradeName,dealerCode,zoneId',
            'sector:id,name',
            'salesDetails.product.childCategory'
        ])->latest()->paginate(150); // You can change 100 to 50 or 200

        // Return response
        return response()->json([
            'message' => 'Success!',
            'data' => SalesOrderResource::collection($sales_orders),
            'meta' => [
                'current_page' => $sales_orders->currentPage(),
                'last_page' => $sales_orders->lastPage(),
                'per_page' => $sales_orders->perPage(),
                'total' => $sales_orders->total(),
            ]
        ], 200);
    }

    public function indexEx(Request $request)
    {
        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

        // Filters
        $saleId         = $request->saleId;
        $dealerId       = $request->dealerId;
        $bookingId      = $request->bookingId;
        $salesPointId   = $request->salesPointId;
        $salesPerson    = $request->salesPerson;
        $productId      = $request->productId;
        $childCategoryId = $request->childCategoryId;
        $startDate      = $request->input('startDate', $oneYearAgo);
        $endDate        = $request->input('endDate', $today);
        $status         = $request->status;
        $limit          = $request->input('limit', 100); // Default 100

        $query = SalesOrder::query();

        // Apply filters
        if ($saleId) {
            $query->where('saleId', 'LIKE', '%' . $saleId . '%');
        }
        if ($dealerId) {
            $query->where('dealerId', $dealerId);
        }
        if ($bookingId) {
            $query->where('bookingId', $bookingId);
        }
        if ($salesPointId) {
            $query->where('salesPointId', $salesPointId);
        }
        if ($salesPerson) {
            $query->where('salesPerson', $salesPerson);
        }
        if ($productId) {
            $query->whereHas('salesDetails', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }
        if ($childCategoryId) {
            $query->whereHas('salesDetails.product', function ($q) use ($childCategoryId) {
                $q->where('childCategoryId', $childCategoryId);
            });
        }
        if ($startDate && $endDate) {
            $query->whereBetween('invoiceDate', [$startDate, $endDate]);
        }
        if ($status) {
            $query->where('status', $status);
        }

        // Eager load related models and paginate by limit
        $sales_orders = $query->with([
            'dealer:id,tradeName,dealerCode,zoneId',
            'sector:id,name',
            'salesDetails.product.childCategory'
        ])->latest()->paginate($limit);

        // Return paginated response
        return response()->json([
            'message' => 'Success!',
            'data' => SalesOrderResource::collection($sales_orders),
            'meta' => [
                'current_page' => $sales_orders->currentPage(),
                'last_page' => $sales_orders->lastPage(),
                'per_page' => $sales_orders->perPage(),
                'total' => $sales_orders->total(),
            ]
        ], 200);
    }

    public function index(Request $request)
    {
        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

        // Filters
        $saleId         = $request->saleId;
        $dealerId       = $request->dealerId;
        $bookingId      = $request->bookingId;
        $salesPointId   = $request->salesPointId;
        $salesPerson    = $request->salesPerson;
        $productId      = $request->productId;
        $childCategoryId = $request->childCategoryId;
        $startDate      = $request->input('startDate', $oneYearAgo);
        $endDate        = $request->input('endDate', $today);
        $status         = $request->status;
        $limit          = $request->input('limit', 100); // Default 100

        //$query = SalesOrder::query();
        $query = SalesOrder::query()
        ->select('sales_orders.*')
        ->selectSub(function ($sub) {
            $sub->from('sales_returns')
                ->join('sales_return_details', 'sales_return_details.saleReturnId', '=', 'sales_returns.id')
                ->whereColumn('sales_returns.saleId', 'sales_orders.id')
                ->where('sales_returns.status', 'approved')
                ->whereNull('sales_returns.deleted_at')
                ->selectRaw('COALESCE(SUM(CAST("rQty" AS double precision)), 0)');
        }, 'return_qty')
        ->selectSub(function ($sub) {
            $sub->from('sales_returns')
                ->join('sales_return_details', 'sales_return_details.saleReturnId', '=', 'sales_returns.id')
                ->whereColumn('sales_returns.saleId', 'sales_orders.id')
                ->where('sales_returns.status', 'approved')
                ->whereNull('sales_returns.deleted_at')
                ->selectRaw('COALESCE(SUM(CAST("rQty" AS double precision) * "salePrice"), 0)');
        }, 'return_amount')
        ->with([ 'dealer:id,tradeName,dealerCode,zoneId',
        'sector:id,name',
        'salesDetails.product.childCategory']);
        // Apply filters
        if ($saleId) {
            $query->where('saleId', 'LIKE', '%' . $saleId . '%');
        }
        if ($dealerId) {
            $query->where('dealerId', $dealerId);
        }
        if ($bookingId) {
            $query->where('bookingId', $bookingId);
        }
        if ($salesPointId) {
            $query->where('salesPointId', $salesPointId);
        }
        if ($salesPerson) {
            $query->where('salesPerson', $salesPerson);
        }
        if ($productId) {
            $query->whereHas('salesDetails', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }
        if ($childCategoryId) {
            $query->whereHas('salesDetails.product', function ($q) use ($childCategoryId) {
                $q->where('childCategoryId', $childCategoryId);
            });
        }
        if ($startDate && $endDate) {
            $query->whereBetween('invoiceDate', [$startDate, $endDate]);
        }
        if ($status) {
            $query->where('status', $status);
        }

        // Eager load related models and paginate by limit
        $sales_orders = $query->latest()->paginate($limit);

    /**
     * 🟨 Extra Step: Fetch Egg Return Details using same date filter
     */
    $returnRows = DB::table('view_egg_sale_return')
    ->select([
        'return_id',
        'saleReturnId',
        'saleId',
        'egg_order_code',
        'totalAmount',
        'invoiceDate',
        'returnDate',
        'dealername',
        'productId',
        'productname',
        'sizeOrWeight',
        'qty',
        'rQty',
        'salePrice',
        'totalReturnAmount',
    ])
    ->whereBetween('returnDate', [$startDate, $endDate])
    ->orderBy('returnDate', 'asc')
    ->get();


// 🟨 Calculation Part: totalReturnAmount & netAmount
$returnRowsWithCalc = $returnRows->map(function ($item) {
    $item->totalReturnAmount = (float) $item->rQty * (float) $item->salePrice;
    $item->netAmount = (float) $item->totalAmount - $item->totalReturnAmount;
    return $item;
});

// 🟩 Group by return_id
$groupedReturns = $returnRowsWithCalc->groupBy('return_id');

        // Return paginated response
        return response()->json([
            'message' => 'Success!',
            'data' => SalesOrderResource::collection($sales_orders),
            'returns' => $groupedReturns, // ✅ Include return data
            'meta' => [
                'current_page' => $sales_orders->currentPage(),
                'last_page' => $sales_orders->lastPage(),
                'per_page' => $sales_orders->perPage(),
                'total' => $sales_orders->total(),
            ]
        ], 200);
    }


    public function store(SalesOrderRequest $request)
    {
        try {


            DB::beginTransaction();

            $sales_order = new SalesOrder();
            $sales_order->saleId = $request->saleId;
            $sales_order->bookingId = $request->bookingId;
            $sales_order->saleCategoryId = $request->saleCategoryId;
            $sales_order->dealerId = $request->dealerId;
            $sales_order->salesPointId = $request->salesPointId;
            $sales_order->salesDraftId = $request->salesDraftId;
            $sales_order->companyId = 7;
            $sales_order->saleType = $request->saleType;
            $sales_order->salesPerson = $request->salesPerson;
            $sales_order->transportType = $request->transportType;
            $sales_order->outTransportInfo = json_encode($request->outTransportInfo);
            $sales_order->dueAmount = $request->totalAmount;
            $sales_order->totalAmount = $request->totalAmount;
            $sales_order->discount = $request->discount;
            $sales_order->discountType = $request->discountType;
            $sales_order->fDiscount = $request->fDiscount;
            $sales_order->vat = $request->vat;
            $sales_order->invoiceDate = $request->invoiceDate;
            $sales_order->note = $request->note;
            $sales_order->pOverRideBy = $request->pOverRideBy;
            $sales_order->transportCost = $request->transportCost;
            $sales_order->othersCost = json_encode($request->othersCost);
            $sales_order->dueDate = $request->dueDate;
            $sales_order->depotCost = $request->depotCost;
            $sales_order->chartOfHeadId = $request->chartOfHeadId;
            // $sales_order->paymentStatus = $request->paymentStatus;
            $sales_order->paymentStatus = 'DUE';
            $sales_order->billingAddress = $request->billingAddress;
            $sales_order->deliveryAddress = $request->deliveryAddress;
            $sales_order->crBy = auth()->id();
            $sales_order->status = 'pending';

            $sales_order->save();

            //dd($sales_order);


            // Detail input START
            $saleId = $sales_order->id;

            foreach ($request->input('sales_details', []) as $detail) {
                $productId = $detail['productId'];
                $tradePrice = $detail['tradePrice'];
                $salePrice = $detail['salePrice'];
                $qty = $detail['qty'];
                $unitId = $detail['unitId'];
                $unitBatchNo = $detail['unitBatchNo'];

                $dbDetail = new SalesOrderDetails();
                $dbDetail->saleId = $saleId;
                $dbDetail->productId = $productId;
                $dbDetail->tradePrice = $tradePrice;
                $dbDetail->salePrice = $salePrice;
                $dbDetail->qty = $qty;
                $dbDetail->unitId = $unitId;
                $dbDetail->unitBatchNo = $unitBatchNo;
                $dbDetail->save();
            }
            // Detail input END
            DB::commit();

            return response()->json([
                'message' => 'Sales Order created successfully',
                'data' => new SalesOrderResource($sales_order),
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception here
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $sales_order = SalesOrder::find($id);
        if (!$sales_order) {
            return response()->json(['message' => 'Sales Order not found'], 404);
        }
        return new SalesOrderResource($sales_order);
    }

    public function update(SalesOrderRequest $request, $id)
    {
        try {
            $sales_order = SalesOrder::find($id);

            if (!$sales_order) {
                return $this->sendError('Sales Order not found.');
            }

            // Check if the SalesOrder is approved
            if ($sales_order->status === 'approved') {
                return response()->json(['message' => 'Cannot modify products for approved Sales Order.'], 403);
            }

            // Update the main SalesOrder fields
            $sales_order->bookingId = $request->bookingId;
            $sales_order->saleCategoryId = $request->saleCategoryId;
            $sales_order->dealerId = $request->dealerId;
            $sales_order->salesPointId = $request->salesPointId;
            $sales_order->salesDraftId = $request->salesDraftId;
            $sales_order->companyId = 7;
            $sales_order->saleType = $request->saleType;
            $sales_order->salesPerson = $request->salesPerson;
            $sales_order->transportType = $request->transportType;
            $sales_order->outTransportInfo = json_encode($request->outTransportInfo);
            $sales_order->dueAmount = $request->totalAmount;
            $sales_order->totalAmount = $request->totalAmount;
            $sales_order->discount = $request->discount;
            $sales_order->discountType = $request->discountType;
            $sales_order->fDiscount = $request->fDiscount;
            $sales_order->vat = $request->vat;
            $sales_order->invoiceDate = $request->invoiceDate;
            $sales_order->note = $request->note;
            $sales_order->pOverRideBy = $request->pOverRideBy;
            $sales_order->transportCost = $request->transportCost;
            $sales_order->othersCost = json_encode($request->othersCost);
            $sales_order->dueDate = $request->dueDate;
            $sales_order->depotCost = $request->depotCost;
            $sales_order->chartOfHeadId = $request->chartOfHeadId;
            $sales_order->paymentStatus = 'DUE';
            $sales_order->billingAddress = $request->billingAddress;
            $sales_order->deliveryAddress = $request->deliveryAddress;
            // $sales_order->status = $request->status;
            $sales_order->status = 'pending';

            $sales_order->save();

            // Update SalesOrderDetails
            $existingDetailIds = $sales_order->details()->pluck('id')->toArray();

            foreach ($request->input('sales_details', []) as $detail) {
                if (isset($detail['id']) && in_array($detail['id'], $existingDetailIds)) {
                    // Update existing detail
                    $sbDetail = SalesOrderDetails::find($detail['id']);
                    $sbDetail->productId = $detail['productId'];
                    $sbDetail->tradePrice = $detail['tradePrice'];
                    $sbDetail->salePrice = $detail['salePrice'];
                    $sbDetail->qty = $detail['qty'];
                    $sbDetail->unitId = $detail['unitId'];
                    $sbDetail->unitBatchNo = $detail['unitBatchNo'];
                    $sbDetail->save();

                    // Remove updated detail ID from the list of existing IDs
                    $existingDetailIds = array_diff($existingDetailIds, [$sbDetail->id]);
                } else {
                    // Create new detail if not exists
                    $sbDetail = new SalesOrderDetails();
                    $sbDetail->saleId = $sales_order->id;
                    $sbDetail->productId = $detail['productId'];
                    $sbDetail->tradePrice = $detail['tradePrice'];
                    $sbDetail->salePrice = $detail['salePrice'];
                    $sbDetail->qty = $detail['qty'];
                    $sbDetail->unitId = $detail['unitId'];
                    $sbDetail->unitBatchNo = $detail['unitBatchNo'];
                    $sbDetail->save();
                }
            }

            // Delete removed details
            SalesOrderDetails::whereIn('id', $existingDetailIds)->delete();

            return response()->json([
                'message' => 'Sales Order updated successfully',
                'data' => new SalesOrderResource($sales_order),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function statusUpdate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Validate status input
            $request->validate([
                'status' => 'required|string|in:approved,pending,delivered,due,declined'
            ]);

            // Find the sales order
            $sales_order = SalesOrder::find($id);
            if (!$sales_order) {
                return response()->json(['message' => 'Sales Order not found'], 404);
            }

            // Prevent status update if already approved or delivered
            if (in_array($sales_order->status, ['approved', 'delivered'])) {
                return response()->json([
                    'message' => 'Cannot change the status of an Approved or Delivered Sales Order.'
                ], 403);
            }
            if ($sales_order->invoiceDate != now()->toDateString()) {
                return response()->json([
                    'message' => 'Sales Order cannot be approved because the invoice date does not match today\'s date.'
                ], 403);
            }
             Cache::flush();
        // Validate stock if status is approved
            if ($request->status === 'approved') {
                foreach ($sales_order->salesDetails as $detail) {
                    $productId = $detail->productId;
                    $sectorId = $sales_order->salesPointId; // Assuming salesPointId = sectorId
                    $requiredQty = $detail->qty;

                    // Fetch stock from egg_stocks
                    $stock = DB::table('egg_stocks')
                        ->where('sectorId', $sectorId)
                        ->where('productId', $productId)
                        ->whereNull('deleted_at') // Only consider non-deleted stocks
                        ->orderByDesc('trDate') // Get latest stock
                        ->value('closing');

                    if ($stock === null || $stock < $requiredQty) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Insufficient stock for Product ID: {$productId} in Sector ID: {$sectorId}. Available: " . ($stock ?? 0) . ", Required: {$requiredQty}"
                        ], 400);
                    }
                }
            }
            // Update status
            $newStatus = $request->status;
            $sales_order->status = $newStatus;
            $sales_order->appBy = auth()->id();

            // If status is approved
            if ($newStatus === 'approved') {
                foreach ($sales_order->salesDetails as $detail) {
                    // Deduct stock
                    $this->eggStockService->EggstoreOrUpdateStockdeDuction(
                        $sales_order->salesPointId, // Assuming salesPointId as sectorId
                        $detail->productId,
                        $detail->qty,
                        $sales_order->invoiceDate
                    );

                    // Create ledger entry
                    $this->ledgerService->createStockAdjLedgerEntry(
                        $sales_order->salesPointId,
                        $detail->productId,
                        $sales_order->saleId,
                        'EggSalesOrder',
                        $sales_order->invoiceDate,
                        -$detail->qty, // Deduction (negative)
                        'Stock deduction for Sales Order approval'
                    );
                }

                // Add Credit and Debit Accounting Logic
                $voucherNo = $sales_order->saleId;
                $voucherType = 'SalesOrder';
                $voucherDate = $sales_order->invoiceDate;
                $companyId = 7;
                // $chartOfHeadId = $sales_order->chartOfHeadId;
                $chartOfHeadId = 65;

                $dealerHead = AccountLedgerName::where(['partyId' => $sales_order->dealerId, 'partyType' => 'D'])->first();
                $companyHead = AccountLedgerName::where(['id' => $chartOfHeadId])->first();

                if ($dealerHead->current_balance < 0) {
                    $currentBalance = abs($dealerHead->current_balance);
                    if ($currentBalance >= $sales_order->totalAmount) {
                        $invoiceStatus = 'Paid';
                        $invoiceNote = 'Paid in full';
                        $paidAmount = $sales_order->totalAmount;
                        $dueAmount = 0;
                    } else {
                        $invoiceStatus = 'Partial';
                        $invoiceNote = 'Partially paid';
                        $paidAmount = $currentBalance;
                        $dueAmount = $sales_order->totalAmount - $currentBalance;
                    }
                    $this->invoicePayment->setPaymentInvoice(
                        saleInvoiceId: $id,
                        dueAmount: $dueAmount,
                        paidAmount: $paidAmount,
                        paidDate: $sales_order->invoiceDate,
                        note: $invoiceNote,
                        status: $invoiceStatus
                    );
                    $sales_order->paymentStatus = $invoiceStatus;
                    $sales_order->dueAmount = $dueAmount;
                }

                // Credit Logic
                $this->accountCredit->setCreditData(
                    chartOfHeadId: $chartOfHeadId, // Example chart of head for credit (replace with correct ID)
                    companyId: $companyId,
                    voucherNo: $voucherNo,
                    voucherType: $voucherType,
                    voucherDate: $voucherDate,
                    note: 'Sales order approved credit entry',
                    credit: $sales_order->totalAmount
                );

                // Debit Logic
                $this->accountDebit->setDebitData(
                    chartOfHeadId: $dealerHead->id, // Example chart of head for debit (replace with correct ID)
                    companyId: $companyId,
                    voucherNo: $voucherNo,
                    voucherType: $voucherType,
                    voucherDate: $voucherDate,
                    note: 'Sales order approved debit entry',
                    debit: $sales_order->totalAmount
                );

                // transport credit
                if ($sales_order->transportCost > 0) {
                    $transportCOHId = 32; // Transport COH ID selected from chart of head change it with correct ID
                    $this->accountCredit->setCreditData(
                        chartOfHeadId: $transportCOHId,
                        companyId: $companyId,
                        voucherNo: $voucherNo,
                        voucherType: 'TransportationRevenue',
                        voucherDate: $voucherDate,
                        note: 'Sales order approved credit entry',
                        credit: $sales_order->transportCost
                    );
                }

                $dealerHead->update([
                    'current_balance' => $dealerHead->current_balance + $sales_order->totalAmount
                ]);
                $companyHead->update([
                    'current_balance' => $companyHead->current_balance + $sales_order->totalAmount
                ]);
            }

            // Save the updated status
            $sales_order->update();
            DB::commit();

            return response()->json([
                'message' => 'Sales Order Status changed successfully',
                'data' => [
                    'id' => $sales_order->id,
                    'status' => $sales_order->status,
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            // \Log::error('Status Update Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
    {
        $sales_order = SalesOrder::find($id);
        if (!$sales_order) {
            return response()->json(['message' => 'Sales Order not found'], 404);
        }
        $sales_order->delete();
        return response()->json([
            'message' => 'Sales Order deleted successfully',
        ], 200);
    }

    //   public function getOrderList()
    //     {
    //       $orderList = SalesOrder::where('status', 'approved')
    //         ->select('id', 'saleId',)
    //         ->get();
    //       return response()->json([
    //         'data' => $orderList
    //       ], 200);
    //     }

    public function getOrderList()
    {

        $orderList = SalesOrder::with(['dealer'])
            ->where('status', 'approved')
            ->select('id', 'saleId', 'dealerId', 'invoiceDate')
            ->get();


        $orderList = $orderList->map(function ($order) {
            return [
                'id' => $order->id,
                'saleId' => $order->saleId,
                'dealer' => [
                    'id' => $order->dealer->id ?? null,
                    'tradeName' => $order->dealer->tradeName ?? null,
                    'dealerCode' => $order->dealer->dealerCode ?? null,
                    'contactPerson' => $order->dealer->contactPerson ?? null,
                    'phone' => $order->dealer->phone ?? null,
                    'zoneName' => $order->dealer->zone->zoneName ?? null,
                ],
                'invoiceDate' => $order->invoiceDate,


            ];
        });

        return response()->json([
            'data' => $orderList
        ], 200);
    }


    //  dealerId wise
    public function getSalesOrdersData(Request $request)
    {

        $dealerId = $request->input('dealerId');


        $salesOrders = SalesOrder::with(['salesDetails', 'dealer'])
            ->select('id', 'saleId', 'dealerId', 'totalAmount', 'dueAmount', 'invoiceDate', 'dueDate', 'status')
            ->whereIn('status', ['approved', 'delivered'])
            ->whereIn('paymentStatus', ['Partial', 'DUE'])
            ->when($dealerId, function ($query) use ($dealerId) {
                return $query->where('dealerId', $dealerId);
            })
            ->orderBy('id')
            ->get()
            ->map(function ($order) {

                $productNames = $order->salesDetails->pluck('productId')->toArray();
                $concatenatedProductNames = Product::whereIn('id', $productNames)
                    ->pluck('productName')
                    ->join(', ');


                $dealerInfo = ($order->dealer->dealerCode ?? '') . ' ' . ($order->dealer->tradeName ?? '');

                return [
                    'id' => $order->id,
                    'saleId' => $order->saleId,
                    'totalAmount' => $order->totalAmount,
                    'dueAmount' => $order->dueAmount,
                    'dealer' => trim($dealerInfo),
                    'invoiceDate' => $order->invoiceDate,
                    'dueDate' => $order->dueDate,
                    'productNames' => $concatenatedProductNames,
                ];
            });

        return response()->json([
            'message' => 'Sales orders fetched successfully',
            'data' => $salesOrders,
        ]);
    }


    public function getProductDailyPrice(Request $request)
    {
        $productId = $request->input('productId');

        if (!$productId) {
            return response()->json(['error' => 'Product ID is required'], 400);
        }

        $dailyPrice = DailyPrice::where('productId', $productId)
            ->where('status', 'approved')
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$dailyPrice) {
            return response()->json(['error' => 'Price not found for the selected product'], 404);
        }

        return response()->json([
            'tradePrice' => $dailyPrice->currentPrice
        ], 200);
    }



    public function getReOrderList()
    {
        // Get the current date and the date one month ago
        $today = Carbon::today();
        $oneMonthAgo = Carbon::today()->subMonth();

        // Fetch the orders that are approved and have an invoiceDate between today and one month ago
        $orderList = SalesOrder::with(['dealer'])
            ->where('status', 'approved')
            ->whereBetween('invoiceDate', [$oneMonthAgo, $today]) // Filtering the dates
            ->select('id', 'saleId', 'dealerId', 'invoiceDate')
            ->get();

        // Map through the orders to format the response
        $orderList = $orderList->map(function ($order) {
            return [
                'id' => $order->id,
                'saleId' => $order->saleId,
                'dealer' => [
                    'id' => $order->dealer->id ?? null,
                    'tradeName' => $order->dealer->tradeName ?? null,
                    'dealerCode' => $order->dealer->dealerCode ?? null,
                    'contactPerson' => $order->dealer->contactPerson ?? null,
                    'phone' => $order->dealer->phone ?? null,
                    'zoneName' => $order->dealer->zone->zoneName ?? null,
                ],
                'invoiceDate' => $order->invoiceDate,
            ];
        });

        // Return the response with the filtered order list
        return response()->json([
            'data' => $orderList
        ], 200);
    }

}
