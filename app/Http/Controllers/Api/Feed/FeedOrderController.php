<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\FeedOrderRequest;
use App\Http\Resources\Feed\FeedOrderResource;
use App\Models\AccountLedgerName;
use App\Models\DailyPrice;
use App\Models\FeedOrder;
use App\Models\FeedOrderDetails;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\AccountsCreditService;
use App\Services\AccountsDebitService;
use App\Services\FeedProductionLedgerService;
use App\Services\FeedStockService;
use App\Services\AddSmsService;
use App\Services\CacheService;
use Illuminate\Support\Facades\DB;
use App\Services\InvoiceWisePaymentService;
use App\Services\LabourDetailsAddService;
use Carbon\Carbon;
use App\Traits\SectorFilter;
use App\Models\Commission;
class FeedOrderController extends Controller
{
    use SectorFilter;

    private $accountDebit;
    private $accountCredit;
    protected $feedLedgerService;
    protected $cacheService;
    protected $feedStockService;
    protected $invoicePayment;
    protected $labourDetailsAddService;
    protected $sendOrderConfirmationSms;
    public function __construct(
        AccountsDebitService $accountDebit,
        AccountsCreditService $accountCredit,
        FeedProductionLedgerService $feedLedgerService,
        FeedStockService $feedStockService,
        CacheService $cacheService,
        InvoiceWisePaymentService $invoicePayment,
        LabourDetailsAddService $labourDetailsAddService,
        AddSmsService $sendOrderConfirmationSms // Inject it correctly
    ) {
        $this->accountDebit = $accountDebit;
        $this->accountCredit = $accountCredit;
        $this->feedLedgerService = $feedLedgerService;
        $this->feedStockService = $feedStockService;
        $this->cacheService = $cacheService;
        $this->invoicePayment = $invoicePayment;
        $this->labourDetailsAddService = $labourDetailsAddService;
        $this->sendOrderConfirmationSms = $sendOrderConfirmationSms; // ✅ FIXED
    }
    public function index1(Request $request)
    {

        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

        $feedId = $request->feedId ?? null;
        $dealerId = $request->dealerId ?? null;
        $bookingId = $request->bookingId ?? null;
        $salesPointId = $request->salesPointId ?? null;
        $salesPerson = $request->salesPerson ?? null;
        $productId = $request->productId ?? null;
        $childCategoryId = $request->childCategoryId ?? null;

        $startDate = $request->input('startDate', $oneYearAgo);
        $endDate = $request->input('endDate', $today);

        $status = $request->status ?? null;

        $query = FeedOrder::query();

        // Filter by feedId
        if ($feedId) {
            $query->where('feedId', 'LIKE', '%' . $feedId . '%');
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

        // Filter by productId within feedOrderDetails
        if ($productId) {
            $query->whereHas('feedDetails', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }
        // Filter by childCategoryId within feedOrderDetails' products
        if ($childCategoryId) {
            $query->whereHas('feedDetails.product', function ($q) use ($childCategoryId) {
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

        // Fetch feed order with eager loading of related data
        //$feed_orders = $query->latest()->get();
        $feed_orders = $query->with(['dealer', 'sector', 'feedDetails.product.childCategory'])->latest()->get();


        // Check if any feed orders found
        if ($feed_orders->isEmpty()) {
            return response()->json(['message' => 'No Feed Order found', 'data' => []], 200);
        }

        // Use the FeedOrderResource to transform the data
        $transformedFeedOrders = FeedOrderResource::collection($feed_orders);

        // Return FeedOrder transformed with the resource
        return response()->json([
            'message' => 'Success!',
            'data' => $transformedFeedOrders
        ], 200);
    }

    public function index00000(Request $request)
    {
        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

        // Filters
        $feedId         = $request->feedId;
        $dealerId       = $request->dealerId;
        $bookingId      = $request->bookingId;
        $salesPointId   = $request->salesPointId;
        $salesPerson    = $request->salesPerson;
        $productId      = $request->productId;
        $childCategoryId = $request->childCategoryId;
        $startDate      = $request->input('startDate', $oneYearAgo);
        $endDate        = $request->input('endDate', $today);
        $status         = $request->status;
        $limit          = $request->input('limit', 100);

        $userId = auth()->id();
        $canPass = $this->adminFilter($userId);

        $query = FeedOrder::query()
            ->select('feed_orders.*')
            ->selectSub(function ($sub) {
                $sub->from('feed_sales_returns')
                    ->join('feed_sales_return_details', 'feed_sales_return_details.saleReturnId', '=', 'feed_sales_returns.id')
                    ->whereColumn('feed_sales_returns.saleId', 'feed_orders.id')
                    ->where('feed_sales_returns.status', 'approved')
                    ->whereNull('feed_sales_returns.deleted_at')
                    ->selectRaw('COALESCE(SUM(CAST("rQty" AS double precision)), 0)');
            }, 'return_qty')
            ->selectSub(function ($sub) {
                $sub->from('feed_sales_returns')
                    ->join('feed_sales_return_details', 'feed_sales_return_details.saleReturnId', '=', 'feed_sales_returns.id')
                    ->whereColumn('feed_sales_returns.saleId', 'feed_orders.id')
                    ->where('feed_sales_returns.status', 'approved')
                    ->whereNull('feed_sales_returns.deleted_at')
                    ->selectRaw('COALESCE(SUM(CAST("rQty" AS double precision) * "salePrice"), 0)');
            }, 'return_amount')
            ->with(['dealer', 'sector', 'feedDetails.product.childCategory']);

        // Sector filter
        if (!$canPass) {
            $sectorIds = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();

            if (!empty($sectorIds)) {
                $query->whereIn('salesPointId', $sectorIds);
            } else {
                return response()->json(['message' => 'No sector access assigned.'], 403);
            }
        }

        // Additional filters
        if ($feedId) {
            $query->where('feedId', 'LIKE', '%' . $feedId . '%');
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
            $query->whereHas('feedDetails', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }
        if ($childCategoryId) {
            $query->whereHas('feedDetails.product', function ($q) use ($childCategoryId) {
                $q->where('childCategoryId', $childCategoryId);
            });
        }
        if ($startDate && $endDate) {
            $query->whereBetween('invoiceDate', [$startDate, $endDate]);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $feed_orders = $query->latest()->paginate($limit);

        return response()->json([
            'message' => 'Success!',
            'data' => FeedOrderResource::collection($feed_orders),
            'meta' => [
                'current_page' => $feed_orders->currentPage(),
                'last_page' => $feed_orders->lastPage(),
                'per_page' => $feed_orders->perPage(),
                'total' => $feed_orders->total(),
            ]
        ], 200);
    }

    public function index(Request $request)
{
    $oneYearAgo = now()->subYear()->format('Y-m-d');
    $today = today()->format('Y-m-d');

    // Filters
    $feedId          = $request->feedId;
    $dealerId        = $request->dealerId;
    $bookingId       = $request->bookingId;
    $salesPointId    = $request->salesPointId;
    $salesPerson     = $request->salesPerson;
    $productId       = $request->productId;
    $childCategoryId = $request->childCategoryId;
    $startDate       = $request->input('startDate', $oneYearAgo);
    $endDate         = $request->input('endDate', $today);
    $status          = $request->status;
    $limit           = $request->input('limit', 100);

    $userId = auth()->id();
    $canPass = $this->adminFilter($userId);

    $query = FeedOrder::query()
        ->select('feed_orders.*')
        ->selectSub(function ($sub) {
            $sub->from('feed_sales_returns')
                ->join('feed_sales_return_details', 'feed_sales_return_details.saleReturnId', '=', 'feed_sales_returns.id')
                ->whereColumn('feed_sales_returns.saleId', 'feed_orders.id')
                ->where('feed_sales_returns.status', 'approved')
                ->whereNull('feed_sales_returns.deleted_at')
                ->selectRaw('COALESCE(SUM(CAST("rQty" AS double precision)), 0)');
        }, 'return_qty')
        ->selectSub(function ($sub) {
            $sub->from('feed_sales_returns')
                ->join('feed_sales_return_details', 'feed_sales_return_details.saleReturnId', '=', 'feed_sales_returns.id')
                ->whereColumn('feed_sales_returns.saleId', 'feed_orders.id')
                ->where('feed_sales_returns.status', 'approved')
                ->whereNull('feed_sales_returns.deleted_at')
                ->selectRaw('COALESCE(SUM(CAST("rQty" AS double precision) * "salePrice"), 0)');
        }, 'return_amount')
        ->with(['dealer', 'sector', 'feedDetails.product.childCategory']);

    // Sector filter
    if (!$canPass) {
        $sectorIds = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();

        if (!empty($sectorIds)) {
            $query->whereIn('salesPointId', $sectorIds);
        } else {
            return response()->json(['message' => 'No sector access assigned.'], 403);
        }
    }

    // Filters
    if ($feedId) {
        $query->where('feedId', 'LIKE', '%' . $feedId . '%');
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
        $query->whereHas('feedDetails', function ($q) use ($productId) {
            $q->where('productId', $productId);
        });
    }
    if ($childCategoryId) {
        $query->whereHas('feedDetails.product', function ($q) use ($childCategoryId) {
            $q->where('childCategoryId', $childCategoryId);
        });
    }
    if ($startDate && $endDate) {
        $query->whereBetween('invoiceDate', [$startDate, $endDate]);
    }
    if ($status) {
        $query->where('status', $status);
    }

    $feed_orders = $query->latest()->paginate($limit);

    /**
     * 🟨 Extra Step: Fetch Feed Return Details using same date filter
     */
    $returnRows = DB::table('view_feed_sale_return')
        ->select([
            'return_id',
            'saleReturnId',
            'saleId',
            'feed_order_code',
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

    // Add bag calculations
    $returnRowsWithBags = $returnRows->map(function ($item) {
        $size = floatval($item->sizeOrWeight) ?: 0;
        $qty = floatval($item->qty) ?: 0;
        $rQty = floatval($item->rQty) ?: 0;
        $salePrice = floatval($item->salePrice);

        // Bag quantity calculation
        $item->bagQty  = $size > 0 ? round($qty / $size, 2) : 0;
        $item->rBagQty = $size > 0 ? round($rQty / $size, 2) : 0;

        // Return amount using returnBagQty * salePrice (same as Resource)
        $item->totalReturnAmount = round($item->rBagQty * $salePrice, 2);

        // Net amount = totalAmount - return amount
        $item->netAmount = floatval($item->totalAmount) - $item->totalReturnAmount;

        return $item;
    });


    $groupedReturns = $returnRowsWithBags->groupBy('return_id');

    return response()->json([
        'message' => 'Success!',
        'data' => FeedOrderResource::collection($feed_orders),
        'returns' => $groupedReturns, // ✅ Include return data
        'meta' => [
            'current_page' => $feed_orders->currentPage(),
            'last_page'    => $feed_orders->lastPage(),
            'per_page'     => $feed_orders->perPage(),
            'total'        => $feed_orders->total(),
        ]
    ], 200);
}

    public function store(FeedOrderRequest $request)
    {
        try {


            DB::beginTransaction();

            $feed_order = new FeedOrder();
            $feed_order->feedId = $request->feedId;
            $feed_order->bookingId = $request->bookingId;
            $feed_order->saleCategoryId = $request->saleCategoryId;
            $feed_order->subCategoryId = $request->subCategoryId;
            $feed_order->childCategoryId = $request->childCategoryId;
            $feed_order->dealerId = $request->dealerId;
            $feed_order->salesPointId = $request->salesPointId;
            $feed_order->feedDraftId = $request->feedDraftId;
            $feed_order->companyId = 2;
            $feed_order->saleType = $request->saleType;
            $feed_order->salesPerson = $request->salesPerson;
            $approvedCommission = Commission::where('dealerId', $request->dealerId)
                ->where('status', 'approved')
                ->first();
            $feed_order->commissionId = $approvedCommission?->id ?? null;
            $feed_order->transportType = $request->transportType;
            $feed_order->loadBy = $request->loadBy;
            $feed_order->isLabourBill = $request->isLabourBill;
            $feed_order->transportBy = $request->transportBy;
            $feed_order->outTransportInfo = json_encode($request->outTransportInfo);
            $feed_order->subTotal = $request->subTotal;
            $feed_order->dueAmount = $request->totalAmount;
            $feed_order->totalAmount = $request->totalAmount;
            $feed_order->discount = $request->discount;
            $feed_order->discountType = $request->discountType;
            $feed_order->fDiscount = $request->fDiscount;
            $feed_order->vat = $request->vat;
            $feed_order->invoiceDate = $request->invoiceDate;
            $feed_order->note = $request->note;
            $feed_order->pOverRideBy = $request->pOverRideBy;
            $feed_order->transportCost = $request->transportCost;
            $feed_order->othersCost = json_encode($request->othersCost);
            $feed_order->dueDate = $request->dueDate;
            $feed_order->depotCost = $request->depotCost;
            $feed_order->chartOfHeadId = $request->chartOfHeadId;
            $feed_order->paymentStatus = 'DUE';
            $feed_order->billingAddress = $request->billingAddress;
            $feed_order->deliveryAddress = $request->deliveryAddress;
            $feed_order->crBy = auth()->id();
            $feed_order->status = 'pending';

            $feed_order->save();

            //dd($feed_order);


            // Detail input START
            $feedId = $feed_order->id;

            foreach ($request->input('feed_details', []) as $detail) {
                $productId = $detail['productId'];
                $tradePrice = $detail['tradePrice'];
                $salePrice = $detail['salePrice'];
                $qty = $detail['qty'];
                $unitId = $detail['unitId'];
                $unitBatchNo = $detail['unitBatchNo'];

                $dbDetail = new FeedOrderDetails();
                $dbDetail->feedId = $feedId;
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
                'message' => 'Feed Order created successfully',
                'data' => new FeedOrderResource($feed_order),
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception here
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
 }


    public function show($id)
    {
        $feed_order = FeedOrder::find($id);
        if (!$feed_order) {
            return response()->json(['message' => 'Feed Order not found'], 404);
        }
        return new FeedOrderResource($feed_order);
    }


    public function update(FeedOrderRequest $request,  $id)
    {
        try {
            $feed_order = FeedOrder::find($id);

            if (!$feed_order) {
                return $this->sendError('Feed Order not found.');
            }

            // Check if the FeedOrder is approved
            if ($feed_order->status === 'approved') {
                return response()->json(['message' => 'Cannot modify products for approved Feed Order.'], 403);
            }

            // Update the main FeedOrder fields
            $feed_order->bookingId = $request->bookingId;
            $feed_order->saleCategoryId = $request->saleCategoryId;
            $feed_order->subCategoryId = $request->subCategoryId;
            $feed_order->childCategoryId = $request->childCategoryId;
            $feed_order->dealerId = $request->dealerId;
            $feed_order->salesPointId = $request->salesPointId;
            $feed_order->feedDraftId = $request->feedDraftId;
            $feed_order->companyId = 2;
            $feed_order->saleType = $request->saleType;
            $feed_order->salesPerson = $request->salesPerson;
            $approvedCommission = Commission::where('dealerId', $request->dealerId)
                ->where('status', 'approved')
                ->first();

            $feed_order->commissionId = $approvedCommission?->id ?? null;
            $feed_order->transportType = $request->transportType;
            $feed_order->loadBy = $request->loadBy;
            $feed_order->isLabourBill = $request->isLabourBill;
            $feed_order->transportBy = $request->transportBy;
            $feed_order->outTransportInfo = json_encode($request->outTransportInfo);
            $feed_order->subTotal = $request->subTotal;
            $feed_order->dueAmount = $request->totalAmount;
            $feed_order->totalAmount = $request->totalAmount;
            $feed_order->discount = $request->discount;
            $feed_order->discountType = $request->discountType;
            $feed_order->fDiscount = $request->fDiscount;
            $feed_order->vat = $request->vat;
            $feed_order->invoiceDate = $request->invoiceDate;
            $feed_order->note = $request->note;
            $feed_order->pOverRideBy = $request->pOverRideBy;
            $feed_order->transportCost = $request->transportCost;
            $feed_order->othersCost = json_encode($request->othersCost);
            $feed_order->dueDate = $request->dueDate;
            $feed_order->depotCost = $request->depotCost;
            $feed_order->chartOfHeadId = $request->chartOfHeadId;
            $feed_order->paymentStatus = 'DUE';
            $feed_order->billingAddress = $request->billingAddress;
            $feed_order->deliveryAddress = $request->deliveryAddress;
            // $feed_order->status = $request->status;
            $feed_order->status = 'pending';

            $feed_order->save();

            // Update FeedOrderDetails
            $existingDetailIds = $feed_order->details()->pluck('id')->toArray();

            foreach ($request->input('feed_details', []) as $detail) {
                if (isset($detail['id']) && in_array($detail['id'], $existingDetailIds)) {
                    // Update existing detail
                    $sbDetail = FeedOrderDetails::find($detail['id']);
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
                    $sbDetail = new FeedOrderDetails();
                    $sbDetail->feedId = $feed_order->id;
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
            FeedOrderDetails::whereIn('id', $existingDetailIds)->delete();

            return response()->json([
                'message' => 'Feed Order updated successfully',
                'data' => new FeedOrderResource($feed_order),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
     }

    // public function statusUpdate(Request $request, $id)
    // {
    //     DB::beginTransaction();
    //     try {
    //         // Validate status input
    //         $request->validate([
    //             'status' => 'required|string|in:approved,pending,delivered,due,declined'
    //         ]);

    //         // Find the feed order
    //         $feed_order = FeedOrder::find($id);
    //         if (!$feed_order) {
    //             return response()->json(['message' => 'Feed Order not found'], 404);
    //         }

    //         // Prevent status update if already approved or delivered
    //         if (in_array($feed_order->status, ['approved', 'delivered'])) {
    //             return response()->json([
    //                 'message' => 'Cannot change the status of an Approved or Delivered Sales Order.'
    //             ], 403);
    //         }

    //         // Update status
    //         $newStatus = $request->status;
    //         $feed_order->status = $newStatus;
    //         $feed_order->appBy = auth()->id();

    //         // If status is approved
    //         if ($newStatus === 'approved') {
    //             foreach ($feed_order->feedDetails as $detail) {
    //                 // Deduct stock
    //                 $this->feedStockService->FeedstoreOrUpdateStockdeDuction(
    //                     $feed_order->salesPointId, // Assuming salesPointId as sectorId
    //                     $detail->productId,
    //                     $detail->qty,
    //                     $feed_order->invoiceDate
    //                 );

    //                 // Create ledger entry
    //                 $this->feedLedgerService->createFeedStockAdjLedgerEntry(
    //                     $feed_order->salesPointId,
    //                     $detail->productId,
    //                     $feed_order->feedId,
    //                     'FeedSalesOrder',
    //                     $feed_order->invoiceDate,
    //                     -$detail->qty, // Deduction (negative)
    //                     'Stock deduction for feed Order approval'
    //                 );
    //             }

    //             // Add Credit and Debit Accounting Logic
    //             $voucherNo = $feed_order->feedId;
    //             $voucherType = 'SalesOrder';
    //             $voucherDate = $feed_order->invoiceDate;
    //             $companyId = 2;
    //             // $chartOfHeadId = $feed_order->chartOfHeadId;
    //             $chartOfHeadId = 96;

    //             $dealerHead = AccountLedgerName::where(['partyId' => $feed_order->dealerId, 'partyType' => 'D'])->first();
    //             $companyHead = AccountLedgerName::where(['id' => $chartOfHeadId])->first();

    //             if ($dealerHead->current_balance < 0) {
    //                 $currentBalance = abs($dealerHead->current_balance);
    //                 if ($currentBalance >= $feed_order->totalAmount) {
    //                     $invoiceStatus = 'Paid';
    //                     $invoiceNote = 'Paid in full';
    //                     $paidAmount = $feed_order->totalAmount;
    //                     $dueAmount = 0;
    //                 } else {
    //                     $invoiceStatus = 'Partial';
    //                     $invoiceNote = 'Partially paid';
    //                     $paidAmount = $currentBalance;
    //                     $dueAmount = $feed_order->totalAmount - $currentBalance;
    //                 }
    //                 $this->invoicePayment->setPaymentInvoice(
    //                     saleInvoiceId: $id,
    //                     dueAmount: $dueAmount,
    //                     paidAmount: $paidAmount,
    //                     paidDate: $feed_order->invoiceDate,
    //                     note: $invoiceNote,
    //                     status: $invoiceStatus
    //                 );
    //                 $feed_order->paymentStatus = $invoiceStatus;
    //                 $feed_order->dueAmount = $dueAmount;
    //             }

    //             // Credit Logic
    //             $this->accountCredit->setCreditData(
    //                 chartOfHeadId: $chartOfHeadId, // Example chart of head for credit (replace with correct ID)
    //                 companyId: $companyId,
    //                 voucherNo: $voucherNo,
    //                 voucherType: $voucherType,
    //                 voucherDate: $voucherDate,
    //                 note: 'Feed order approved credit entry',
    //                 credit: $feed_order->totalAmount
    //             );

    //             // Debit Logic
    //             $this->accountDebit->setDebitData(
    //                 chartOfHeadId: $dealerHead->id, // Example chart of head for debit (replace with correct ID)
    //                 companyId: $companyId,
    //                 voucherNo: $voucherNo,
    //                 voucherType: $voucherType,
    //                 voucherDate: $voucherDate,
    //                 note: 'Feed order approved debit entry',
    //                 debit: $feed_order->totalAmount
    //             );

    //             // transport credit
    //             if ($feed_order->transportCost > 0) {
    //                 $transportCOHId = 32; // Transport COH ID selected from chart of head change it with correct ID
    //                 $this->accountCredit->setCreditData(
    //                     chartOfHeadId: $transportCOHId,
    //                     companyId: $companyId,
    //                     voucherNo: $voucherNo,
    //                     voucherType: 'TransportationRevenue',
    //                     voucherDate: $voucherDate,
    //                     note: "\"{$feed_order->id}-{$feed_order->feedId}\" Feed order approved credit entry",
    //                     credit: $feed_order->transportCost
    //                 );
    //             }
    //                   // depot credit
    //                   if ($feed_order->depotCost > 0) {
    //                     $depotheadID = 98; // Transport COH ID selected from chart of head change it with correct ID
    //                     $this->accountCredit->setCreditData(
    //                         chartOfHeadId: $depotheadID,
    //                         companyId: $companyId,
    //                         voucherNo: $voucherNo,
    //                         voucherType: 'DepotRevenue',
    //                         voucherDate: $voucherDate,
    //                         note: "\"{$feed_order->id}-{$feed_order->feedId}\" Feed order approved credit entry",
    //                         credit: $feed_order->depotCost
    //                     );
    //                 }
    //             $dealerHead->update([
    //                 'current_balance' => $dealerHead->current_balance + $feed_order->totalAmount
    //             ]);
    //             $companyHead->update([
    //                 'current_balance' => $companyHead->current_balance + $feed_order->totalAmount
    //             ]);
    //         }

    //         // Save the updated status
    //         $feed_order->update();
    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Feed Order Status changed successfully',
    //             'data' => [
    //                 'id' => $feed_order->id,
    //                 'status' => $feed_order->status,
    //             ]
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         // \Log::error('Status Update Error: ' . $e->getMessage());
    //         return response()->json([
    //             'message' => 'An error occurred: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    //2
    // public function statusUpdate(Request $request, $id)
    // {
    //     DB::beginTransaction();
    //     try {
    //         // Validate status input
    //         $request->validate([
    //             'status' => 'required|string|in:approved,pending,delivered,due,declined'
    //         ]);

    //         // Find the feed order
    //         $feed_order = FeedOrder::find($id);
    //         if (!$feed_order) {
    //             return response()->json(['message' => 'Feed Order not found'], 404);
    //         }

    //         // Prevent status update if already approved or delivered
    //         if (in_array($feed_order->status, ['approved', 'delivered'])) {
    //             return response()->json([
    //                 'message' => 'Cannot change the status of an Approved or Delivered Sales Order.'
    //             ], 403);
    //         }



    //         // Update status
    //         $newStatus = $request->status;
    //         $feed_order->status = $newStatus;
    //         $feed_order->appBy = auth()->id();

    //         // If status is approved
    //         if ($newStatus === 'approved') {

    //             foreach ($feed_order->feedDetails as $detail) {
    //                 $qty = $detail->qty;
    //                 // Deduct stock
    //                 $this->feedStockService->FeedstoreOrUpdateStockdeDuction(
    //                     $feed_order->salesPointId, // Assuming salesPointId as sectorId
    //                     $detail->productId,
    //                     $detail->qty,
    //                     $feed_order->invoiceDate
    //                 );

    //                 // Create ledger entry
    //                 $this->feedLedgerService->createFeedStockAdjLedgerEntry(
    //                     $feed_order->salesPointId,
    //                     $detail->productId,
    //                     $feed_order->feedId,
    //                     'FeedSalesOrder',
    //                     $feed_order->invoiceDate,
    //                     -$detail->qty, // Deduction (negative)
    //                     'Stock deduction for feed Order approval'
    //                 );

    //                 //
    //                 $this->labourDetailsAddService->addLabourDetail(
    //                     labourId: $feed_order->loadBy ?? null,
    //                     depotId: $feed_order->salesPointId,
    //                     transactionId: $feed_order->feedId,
    //                     transactionType: 'productionSale',
    //                     workType: 'Feed Order',
    //                     tDate: $feed_order->invoiceDate,
    //                     qty: $qty,
    //                     status: 'approved'
    //                 );

    //             }

    //             // Add Credit and Debit Accounting Logic
    //             $voucherNo = $feed_order->feedId;
    //             $voucherType = 'SalesOrder';
    //             $voucherDate = $feed_order->invoiceDate;
    //             $companyId = 2;
    //             // $chartOfHeadId = $feed_order->chartOfHeadId;
    //             $chartOfHeadId = 96;

    //             $dealerHead = AccountLedgerName::where(['partyId' => $feed_order->dealerId, 'partyType' => 'D'])->first();
    //             $companyHead = AccountLedgerName::where(['id' => $chartOfHeadId])->first();

    //             if ($dealerHead->current_balance < 0) {
    //                 $currentBalance = abs($dealerHead->current_balance);
    //                 if ($currentBalance >= $feed_order->totalAmount) {
    //                     $invoiceStatus = 'Paid';
    //                     $invoiceNote = 'Paid in full';
    //                     $paidAmount = $feed_order->totalAmount;
    //                     $dueAmount = 0;
    //                 } else {
    //                     $invoiceStatus = 'Partial';
    //                     $invoiceNote = 'Partially paid';
    //                     $paidAmount = $currentBalance;
    //                     $dueAmount = $feed_order->totalAmount - $currentBalance;
    //                 }
    //                 $this->invoicePayment->setPaymentInvoice(
    //                     saleInvoiceId: $id,
    //                     dueAmount: $dueAmount,
    //                     paidAmount: $paidAmount,
    //                     paidDate: $feed_order->invoiceDate,
    //                     note: $invoiceNote,
    //                     status: $invoiceStatus
    //                 );
    //                 $feed_order->paymentStatus = $invoiceStatus;
    //                 $feed_order->dueAmount = $dueAmount;
    //             }

    //             // Credit Logic
    //             $this->accountCredit->setCreditData(
    //                 chartOfHeadId: $chartOfHeadId, // Example chart of head for credit (replace with correct ID)
    //                 companyId: $companyId,
    //                 voucherNo: $voucherNo,
    //                 voucherType: $voucherType,
    //                 voucherDate: $voucherDate,
    //                 note: 'Feed order approved credit entry',
    //                 credit: $feed_order->totalAmount
    //             );

    //             // Debit Logic
    //             $this->accountDebit->setDebitData(
    //                 chartOfHeadId: $dealerHead->id, // Example chart of head for debit (replace with correct ID)
    //                 companyId: $companyId,
    //                 voucherNo: $voucherNo,
    //                 voucherType: $voucherType,
    //                 voucherDate: $voucherDate,
    //                 note: 'Feed order approved debit entry',
    //                 debit: $feed_order->totalAmount
    //             );
    //             $transportHead = AccountLedgerName::where('id', 32)->first();

    //          // transport credit
    //             $transportCost = (float) $feed_order->transportCost;
    //             if ($transportCost > 0) {
    //                 $transportCOHId = 32; // Replace with correct Chart of Head ID
    //                 $this->accountCredit->setCreditData(
    //                     chartOfHeadId: $transportCOHId,
    //                     companyId: $companyId,
    //                     voucherNo: $voucherNo,
    //                     voucherType: 'FeedTransportationRevenue',
    //                     voucherDate: $voucherDate,
    //                     note: "\"{$feed_order->id}-{$feed_order->feedId}\" Feed order approved credit entry",
    //                     credit: -$transportCost
    //                 );

    //             }


    //             $depotCost = (float) $feed_order->depotCost;
    //             if ($depotCost > 0) {
    //                 $depotHead = AccountLedgerName::where([
    //                     'partyId' => $feed_order->salesPointId,
    //                     'partyType' => 'S', // S = SalesPoint/Depot
    //                 ])->first();

    //                 if ($depotHead) {
    //                     $this->accountCredit->setCreditData(
    //                         chartOfHeadId: $depotHead->id, // 🔥 Dynamic ChartOfHeadId
    //                         companyId: $companyId,
    //                         voucherNo: $voucherNo,
    //                         voucherType: 'DepotRevenue',
    //                         voucherDate: $voucherDate,
    //                         note: "\"{$feed_order->id}-{$feed_order->feedId}\" Feed order approved credit entry",
    //                         credit: -$depotCost
    //                     );

    //                     // 🔥 Update depot ledger current balance
    //                     $depotHead->update([
    //                         'current_balance' => $depotHead->current_balance + $depotCost
    //                     ]);
    //                 }
    //             }

    //             $dealerHead->update([
    //                 'current_balance' => $dealerHead->current_balance + $feed_order->totalAmount
    //             ]);
    //             $companyHead->update([
    //                 'current_balance' => $companyHead->current_balance + $feed_order->totalAmount
    //             ]);

    //             if ($transportHead) {
    //                 $transportHead->update([
    //                     'current_balance' => $transportHead->current_balance + $transportCost
    //                 ]);
    //             }
    //         }

    //         // Save the updated status
    //         $feed_order->update();
    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Feed Order Status changed successfully',
    //             'data' => [
    //                 'id' => $feed_order->id,
    //                 'status' => $feed_order->status,
    //             ]
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         // \Log::error('Status Update Error: ' . $e->getMessage());
    //         return response()->json([
    //             'message' => 'An error occurred: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    // with booking status
    public function statusUpdate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Validate status input
            $request->validate([
                'status' => 'required|string|in:approved,pending,delivered,due,declined'
            ]);

            // Find the feed order
            $feed_order = FeedOrder::find($id);
            if (!$feed_order) {
                return response()->json(['message' => 'Feed Order not found'], 404);
            }

            // Prevent status update if already approved or delivered
            if (in_array($feed_order->status, ['approved', 'delivered'])) {
                return response()->json([
                    'message' => 'Cannot change the status of an Approved or Delivered Sales Order.'
                ], 403);
            }



            // Update status
            $newStatus = $request->status;
            $feed_order->status = $newStatus;
            $feed_order->appBy = auth()->id();

            // If status is approved
            if ($newStatus === 'approved') {


            if ($feed_order->invoiceDate !== now()->toDateString()) {
                    return response()->json([
                        'message' => 'Status cannot be approved because Invoice Date does not match today\'s date.'
                    ], 422);
            }
                // 👇 Booking status update logic here
            if ($feed_order->bookingId) {
                $booking = \App\Models\FeedBooking::find($feed_order->bookingId);
                if ($booking) {
                    $booking->status = 'sold';
                    $booking->save();
                }
            }

                foreach ($feed_order->feedDetails as $detail) {
                    $qty = $detail->qty;
                    // Deduct stock
                    $this->feedStockService->FeedstoreOrUpdateStockdeDuction(
                        $feed_order->salesPointId, // Assuming salesPointId as sectorId
                        $detail->productId,
                        $detail->qty,
                        $feed_order->invoiceDate
                    );

                    // Create ledger entry
                    $this->feedLedgerService->createFeedStockAdjLedgerEntry(
                        $feed_order->salesPointId,
                        $detail->productId,
                        $feed_order->feedId,
                        'FeedSalesOrder',
                        $feed_order->invoiceDate,
                        -$detail->qty, // Deduction (negative)
                        'Stock deduction for feed Order approval'
                    );

                    // $this->labourDetailsAddService->addLabourDetail(
                    //     labourId: $feed_order->loadBy ?? null,
                    //     depotId: $feed_order->salesPointId,
                    //     transactionId: $feed_order->feedId,
                    //     transactionType: 'productionSale',
                    //     workType: 'Feed Order',
                    //     tDate: $feed_order->invoiceDate,
                    //     qty: $qty,
                    //     status: 'approved'
                    // );

                      // ✅ Only add labour detail if loadBy exists
                      if (!empty($feed_order->loadBy)) {
                        $this->labourDetailsAddService->addLabourDetail(
                        labourId: $feed_order->loadBy,
                        depotId: $feed_order->salesPointId,
                        transactionId: $feed_order->feedId,
                        transactionType: 'productionSale',
                        workType: 'Feed Order',
                        tDate: $feed_order->invoiceDate,
                        qty: $qty,
                        status: 'approved'
                        );
                    }

                }

                // Add Credit and Debit Accounting Logic
                $voucherNo = $feed_order->feedId;
                $voucherType = 'SalesOrder';
                $voucherDate = $feed_order->invoiceDate;
                $companyId = 2;
                // $chartOfHeadId = $feed_order->chartOfHeadId;
                $chartOfHeadId = 96;

                $dealerHead = AccountLedgerName::where(['partyId' => $feed_order->dealerId, 'partyType' => 'D'])->first();
                $companyHead = AccountLedgerName::where(['id' => $chartOfHeadId])->first();

                if ($dealerHead->current_balance < 0) {
                    $currentBalance = abs($dealerHead->current_balance);
                    if ($currentBalance >= $feed_order->totalAmount) {
                        $invoiceStatus = 'Paid';
                        $invoiceNote = 'Paid in full';
                        $paidAmount = $feed_order->totalAmount;
                        $dueAmount = 0;
                    } else {
                        $invoiceStatus = 'Partial';
                        $invoiceNote = 'Partially paid';
                        $paidAmount = $currentBalance;
                        $dueAmount = $feed_order->totalAmount - $currentBalance;
                    }
                    $this->invoicePayment->setPaymentInvoice(
                        saleInvoiceId: $id,
                        dueAmount: $dueAmount,
                        paidAmount: $paidAmount,
                        paidDate: $feed_order->invoiceDate,
                        note: $invoiceNote,
                        status: $invoiceStatus
                    );
                    $feed_order->paymentStatus = $invoiceStatus;
                    $feed_order->dueAmount = $dueAmount;
                }

                // Credit Logic
                $this->accountCredit->setCreditData(
                    chartOfHeadId: $chartOfHeadId, // Example chart of head for credit (replace with correct ID)
                    companyId: $companyId,
                    voucherNo: $voucherNo,
                    voucherType: $voucherType,
                    voucherDate: $voucherDate,
                    note: 'Feed order approved credit entry',
                    credit: $feed_order->totalAmount
                );

                // Debit Logic
                $this->accountDebit->setDebitData(
                    chartOfHeadId: $dealerHead->id, // Example chart of head for debit (replace with correct ID)
                    companyId: $companyId,
                    voucherNo: $voucherNo,
                    voucherType: $voucherType,
                    voucherDate: $voucherDate,
                    note: 'Feed order approved debit entry',
                    debit: $feed_order->totalAmount
                );
                $transportHead = AccountLedgerName::where('id', 32)->first();

             // transport credit
                $transportCost = (float) $feed_order->transportCost;
                if ($transportCost > 0) {
                    $transportCOHId = 115; // Replace with correct Chart of Head ID
                    $this->accountCredit->setCreditData(
                        chartOfHeadId: $transportCOHId,
                        companyId: $companyId,
                        voucherNo: $voucherNo,
                        voucherType: 'FeedTransportationRevenue',
                        voucherDate: $voucherDate,
                        note: "\"{$feed_order->id}-{$feed_order->feedId}\" Feed order approved credit entry",
                        credit: -$transportCost
                    );

                }


                $depotCost = (float) $feed_order->depotCost;
                if ($depotCost > 0) {
                    $depotHead = AccountLedgerName::where([
                        'partyId' => $feed_order->salesPointId,
                        'partyType' => 'S', // S = SalesPoint/Depot
                    ])->first();

                    if ($depotHead) {
                        $this->accountCredit->setCreditData(
                            chartOfHeadId: $depotHead->id, // 🔥 Dynamic ChartOfHeadId
                            companyId: $companyId,
                            voucherNo: $voucherNo,
                            voucherType: 'DepotRevenue',
                            voucherDate: $voucherDate,
                            note: "\"{$feed_order->id}-{$feed_order->feedId}\" Feed order approved credit entry",
                            credit: -$depotCost
                        );

                        // 🔥 Update depot ledger current balance
                        $depotHead->update([
                            'current_balance' => $depotHead->current_balance + $depotCost
                        ]);
                    }
                }

                $dealerHead->update([
                    'current_balance' => $dealerHead->current_balance + $feed_order->totalAmount
                ]);
                $companyHead->update([
                    'current_balance' => $companyHead->current_balance + $feed_order->totalAmount
                ]);

                if ($transportHead) {
                    $transportHead->update([
                        'current_balance' => $transportHead->current_balance + $transportCost
                    ]);
                }
            try {
                $dealer = $feed_order->dealer;

                if ($dealer && $dealer->phone) {
                    // 🧱 Build product message
                    $productLines = $feed_order->feedDetails->map(function ($item) {
                        $productName = $item->product->shortName ?? $item->product->name ?? 'Product';
                        return "$productName x {$item->qty} bags";
                    })->implode(', ');

                    // ✉️ Final SMS body
                    $smsBody = "Dear {$dealer->tradeName}, Your Order#{$feed_order->feedId} has been confirmed.#$productLines. From (Peoples Feed)";

                    // ✅ Send SMS
                    $this->sendOrderConfirmationSms->sendCustomSms(
                        $dealer->phone,
                        $smsBody
                    );
                }
            } catch (\Exception $e) {
                // \Log::error('SMS sending failed: ' . $e->getMessage());
            }


            }

            // Save the updated status
            $feed_order->update();
            DB::commit();

            return response()->json([
                'message' => 'Feed Order Status changed successfully',
                'data' => [
                    'id' => $feed_order->id,
                    'status' => $feed_order->status,
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
        $feed_order = FeedOrder::find($id);
        if (!$feed_order) {
            return response()->json(['message' => 'Feed Order not found'], 404);
        }
        $feed_order->delete();
        return response()->json([
            'message' => 'Feed Order deleted successfully',
        ], 200);
    }

    public function getFeedOrderList()
    {

        $orderList = FeedOrder::with(['dealer'])
            ->where('status', 'approved')
            ->select('id', 'feedId', 'dealerId','invoiceDate')
            ->get();


        $orderList = $orderList->map(function ($order) {
            return [
                'id' => $order->id,
                'feedId' => $order->feedId,
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
    public function getFeedOrdersData(Request $request)
    {

        $dealerId = $request->input('dealerId');


        $feedOrders = FeedOrder::with(['feedDetails', 'dealer'])
            ->select('id', 'feedId', 'dealerId', 'subTotal','totalAmount', 'dueAmount', 'invoiceDate', 'dueDate', 'status')
            ->whereIn('status', ['approved', 'delivered'])
            ->whereIn('paymentStatus', ['Partial', 'DUE'])
            ->when($dealerId, function ($query) use ($dealerId) {
                return $query->where('dealerId', $dealerId);
            })
            ->orderBy('id')
            ->get()
            ->map(function ($order) {

                $productNames = $order->feedDetails->pluck('productId')->toArray();
                $concatenatedProductNames = Product::whereIn('id', $productNames)
                    ->pluck('productName')
                    ->join(', ');


                $dealerInfo = ($order->dealer->dealerCode ?? '') . ' ' . ($order->dealer->tradeName ?? '');

                return [
                    'id' => $order->id,
                    'feedId' => $order->feedId,
                    'subTotal' => $order->subTotal,
                    'totalAmount' => $order->totalAmount,
                    'dueAmount' => $order->dueAmount,
                    'dealer' => trim($dealerInfo),
                    'invoiceDate' => $order->invoiceDate,
                    'dueDate' => $order->dueDate,
                    'productNames' => $concatenatedProductNames,
                ];
            });

        return response()->json([
            'message' => 'Feed orders fetched successfully',
            'data' => $feedOrders,
        ]);
    }


    public function getFeedProductDailyPrice(Request $request)
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

    public function getFReOrderList()
    {
        // Get the current date and the date one month ago
        $today = Carbon::today();
        $oneMonthAgo = Carbon::today()->subDays(35);


        $orderList = FeedOrder::with(['dealer'])
            ->where('status', 'approved')
            ->whereBetween('invoiceDate', [$oneMonthAgo, $today]) // Filtering the dates
            ->select('id', 'feedId', 'dealerId','invoiceDate')
            ->get();


        $orderList = $orderList->map(function ($order) {
            return [
                'id' => $order->id,
                'feedId' => $order->feedId,
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
}