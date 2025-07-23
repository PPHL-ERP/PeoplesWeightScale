<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\FeedSalesReturnRequest;
use App\Http\Resources\Feed\FeedSalesReturnResource;
use App\Models\AccountLedgerName;
use App\Models\FeedSalesReturn;
use App\Models\FeedSalesReturnDetails;
use App\Services\AccountsCreditService;
use App\Services\AccountsDebitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FeedProductionLedgerService;
use App\Services\FeedStockService;
use App\Services\CacheService;
use App\Services\LabourDetailsAddService;

class FeedSalesReturnController extends Controller
{

    private $accountDebit;
    private $accountCredit;

    protected $cacheService;
    protected $feedStockService;
    protected $feedLedgerService;
    protected $labourDetailsAddService;

    // public function __construct(AccountsDebitService $accountDebit, AccountsCreditService $accountCredit)
    // {
    //   $this->accountDebit = $accountDebit;
    //   $this->accountCredit = $accountCredit;
    // }

    public function __construct(AccountsDebitService $accountDebit, AccountsCreditService $accountCredit,FeedProductionLedgerService $feedLedgerService,CacheService $cacheService, FeedStockService $feedStockService, LabourDetailsAddService $labourDetailsAddService,)
    {
      $this->accountDebit = $accountDebit;
      $this->accountCredit = $accountCredit;
      $this->feedLedgerService = $feedLedgerService;

      $this->cacheService = $cacheService;
      $this->feedStockService = $feedStockService;

      $this->labourDetailsAddService = $labourDetailsAddService;

    }


    public function indexOld(Request $request)
  {

    $oneYearAgo = now()->subYear()->format('Y-m-d');
    $today = today()->format('Y-m-d');

    $saleReturnId = $request->saleReturnId ?? null;
    $saleId = $request->saleId ?? null;
    $dealerId = $request->dealerId ?? null;
    $startDate = $request->input('startDate', $oneYearAgo);
    $endDate = $request->input('endDate', $today);

    $status = $request->status ?? null;
    $productId = $request->productId ?? null;
    $childCategoryId = $request->childCategoryId ?? null;
    $query = FeedSalesReturn::query();

    // Filter by saleReturnId
    if ($saleReturnId) {
      $query->where('saleReturnId', 'LIKE', '%' . $saleReturnId . '%');
    }
    // Filter by saleId
    if ($saleId) {
      $query->orWhere('saleId', $saleId);
    }

    // Filter by dealerId
    if ($dealerId) {
      $query->orWhere('dealerId', $dealerId);
    }

    //Filter Date
    if ($startDate && $endDate) {
      $query->whereBetween('returnDate', [$startDate, $endDate]);
    }

    // Filter by status
    if ($status) {
      $query->where('status', $status);
    }

    // Filter by productId within feedTransferDetails
    if ($productId) {
        $query->whereHas('details', function ($q) use ($productId) {
            $q->where('productId', $productId);
        });
    }
    // Filter by childCategoryId within feedTransferDetails' products
    if ($childCategoryId) {
        $query->whereHas('details.product', function ($q) use ($childCategoryId) {
            $q->where('childCategoryId', $childCategoryId);
        });
    }

    // Fetch sales return with eager loading of related data
    //$sales_returns = $query->latest()->get();
    $sales_returns = $query->with(['details.product.childCategory'])->latest()->get();

    // Check if any sales returns found
    if ($sales_returns->isEmpty()) {
      return response()->json(['message' => 'No Feed Sales Return found', 'data' => []], 200);
    }

    // Use the FeedSalesReturnResource to transform the data
    $transformedSalesReturns = FeedSalesReturnResource::collection($sales_returns);

    // Return FeedSalesReturnResource transformed with the resource
    return response()->json([
      'message' => 'Success!',
      'data' => $transformedSalesReturns
    ], 200);
  }

  public function index(Request $request)
  {

    $oneYearAgo = now()->subYear()->format('Y-m-d');
    $today = today()->format('Y-m-d');

     // Filters
     $saleReturnId    = $request->saleReturnId;
     $saleId       = $request->saleId;
     $dealerId      = $request->dealerId;
     $productId      = $request->productId;
     $childCategoryId = $request->childCategoryId;
     $startDate      = $request->input('startDate', $oneYearAgo);
     $endDate        = $request->input('endDate', $today);
     $status         = $request->status;
     $limit          = $request->input('limit', 100); // Default 100

    $query = FeedSalesReturn::query();

    // Filter by saleReturnId
    if ($saleReturnId) {
      $query->where('saleReturnId', 'LIKE', '%' . $saleReturnId . '%');
    }
    // Filter by saleId
    if ($saleId) {
      $query->where('saleId', $saleId);
    }

    // Filter by dealerId
    if ($dealerId) {
      $query->where('dealerId', $dealerId);
    }

    //Filter Date
    if ($startDate && $endDate) {
      $query->whereBetween('returnDate', [$startDate, $endDate]);
    }

    // Filter by status
    if ($status) {
      $query->where('status', $status);
    }

    // Filter by productId within feedTransferDetails
    if ($productId) {
        $query->whereHas('details', function ($q) use ($productId) {
            $q->where('productId', $productId);
        });
    }
    // Filter by childCategoryId within feedTransferDetails' products
    if ($childCategoryId) {
        $query->whereHas('details.product', function ($q) use ($childCategoryId) {
            $q->where('childCategoryId', $childCategoryId);
        });
    }

    // Fetch sales return with eager loading of related data
    //$sales_returns = $query->latest()->get();
    $sales_returns = $query->with(['details.product.childCategory'])->latest()->paginate($limit);

       // Return paginated response
  return response()->json([
    'message' => 'Success!',
    'data' => FeedSalesReturnResource::collection($sales_returns),
    'meta' => [
        'current_page' => $sales_returns->currentPage(),
        'last_page' => $sales_returns->lastPage(),
        'per_page' => $sales_returns->perPage(),
        'total' => $sales_returns->total(),
    ]
], 200);
  }


    public function store(FeedSalesReturnRequest $request)
    {
        try {


            DB::beginTransaction();

        // Approved return check directly without calling service
            $approvedReturnExists = FeedSalesReturn::where('saleId', $request->saleId)
            ->where('status', 'approved')
            ->exists();

            if ($approvedReturnExists) {
                return response()->json([
                    'message' => 'An approved return already exists for this sale.',
                ], 400);
            }

            $sales_return = new FeedSalesReturn();
            $sales_return->saleReturnId = $request->saleReturnId;
            $sales_return->saleId = $request->saleId;
            $sales_return->dealerId = $request->dealerId;
            $sales_return->returnPurpose = $request->returnPurpose;
            $sales_return->invoiceDate = $request->invoiceDate;
            $sales_return->returnDate = $request->returnDate;
            $sales_return->totalReturnAmount = $request->totalReturnAmount;
            $sales_return->discount = $request->discount;
            $sales_return->note = $request->note;
            $sales_return->isLabourBill = $request->isLabourBill;
            $sales_return->crBy = auth()->id();
            $sales_return->status = 'pending';

            $sales_return->save();

            //dd($sales_return);


            // Detail input START
            $saleReturnId = $sales_return->id;

            foreach ($request->input('sales_return_details', []) as $detail) {
              $productId = $detail['productId'];
              $unitId = $detail['unitId'];
              $tradePrice = $detail['tradePrice'];
              $salePrice = $detail['salePrice'];
              $qty = $detail['qty'];
              $rQty = $detail['rQty'];
              $note = $detail['note'];

              $rdDetail = new FeedSalesReturnDetails();
              $rdDetail->saleReturnId = $saleReturnId;
              $rdDetail->productId = $productId;
              $rdDetail->unitId = $unitId;
              $rdDetail->tradePrice = $tradePrice;
              $rdDetail->salePrice = $salePrice;
              $rdDetail->qty = $qty;
              $rdDetail->rQty = $rQty;
              $rdDetail->note = $note;
              $rdDetail->save();
            }
            // Detail input END
            DB::commit();

            return response()->json([
              'message' => 'Feed Sales Return created successfully',
              'data' => new FeedSalesReturnResource($sales_return),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
    }

    public function show(string $id)
    {
        $sales_return = FeedSalesReturn::find($id);
        if (!$sales_return) {
          return response()->json(['message' => 'Feed Sales Return not found'], 404);
        }
        return new FeedSalesReturnResource($sales_return);
    }


    public function update(FeedSalesReturnRequest $request, string $id)
    {
        try {
            $sales_return = FeedSalesReturn::find($id);

            if (!$sales_return) {
              return response()->json(['message' => 'Feed Sales Return not found.'], 404);
            }

            // Update the main SalesReturn fields
            $sales_return->saleId = $request->saleId;
            $sales_return->dealerId = $request->dealerId;
            $sales_return->returnPurpose = $request->returnPurpose;
            $sales_return->invoiceDate = $request->invoiceDate;
            $sales_return->returnDate = $request->returnDate;
            $sales_return->totalReturnAmount = $request->totalReturnAmount;
            $sales_return->discount = $request->discount;
            $sales_return->note = $request->note;
            $sales_return->isLabourBill = $request->isLabourBill;

            $sales_return->status = 'pending';

            $sales_return->update();

            // Update SalesReturnDetails
            $upDetailIds = $sales_return->details()->pluck('id')->toArray();

            foreach ($request->input('sales_return_details', []) as $detail) {
              if (isset($detail['id']) && in_array($detail['id'], $upDetailIds)) {

                $rdDetail = FeedSalesReturnDetails::find($detail['id']);
                $rdDetail->productId = $detail['productId'];
                $rdDetail->unitId = $detail['unitId'];
                $rdDetail->tradePrice = $detail['tradePrice'];
                $rdDetail->salePrice = $detail['salePrice'];
                $rdDetail->qty = $detail['qty'];
                $rdDetail->rQty = $detail['rQty'];
                $rdDetail->note = $detail['note'];
                $rdDetail->save();

                // Remove updated detail ID from the list of existing IDs
                $upDetailIds = array_diff($upDetailIds, [$rdDetail->id]);
              } else {
                // Create new detail if not exists
                $rdDetail = new FeedSalesReturnDetails();
                $rdDetail->saleReturnId = $sales_return->id;
                $rdDetail->productId = $detail['productId'];
                $rdDetail->unitId = $detail['unitId'];
                $rdDetail->tradePrice = $detail['tradePrice'];
                $rdDetail->salePrice = $detail['salePrice'];
                $rdDetail->qty = $detail['qty'];
                $rdDetail->rQty = $detail['rQty'];
                $rdDetail->note = $detail['note'];
                $rdDetail->save();
              }
            }

            // Delete removed details
            FeedSalesReturnDetails::whereIn('id', $upDetailIds)->delete();

            return response()->json([
              'message' => 'Feed Sales Return updated successfully',
              'data' => new FeedSalesReturnResource($sales_return),
            ], 200);
          } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
    }



    // public function statusUpdate(Request $request, $id)
    // {
    //     DB::beginTransaction();
    //     try {
    //         // Validate the new status value
    //         $request->validate([
    //             'status' => 'required|string|in:approved,declined,pending',
    //         ]);

    //         // Find the sales return record
    //         $sales_return = FeedSalesReturn::with(['feed', 'saleReturnDetails'])->findOrFail($id);

    //         // Prevent any updates if the current status is already approved
    //         if ($sales_return->status === 'approved') {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'The status is already approved and cannot be changed.',
    //             ], 400);
    //         }

    //         // Check if the requested status is the same as the current status
    //         if ($sales_return->status === $request->status) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'The status is already ' . $request->status . '. No changes were made.',
    //             ], 400);
    //         }
    //         // Debugging output
    //         // dd($sales_return->saleReturnDetails->toArray());

    //         $data = $sales_return->feed;

    //         // Update sales return status
    //         $sales_return->status = $request->status;
    //         $sales_return->appBy = auth()->id();
    //         $totalReturnAmount = $sales_return->totalReturnAmount;
    //         $sales_return->save();

    //         // Ensure salesReturnDetails exists before looping
    //         if ($sales_return->saleReturnDetails->isNotEmpty()) {
    //             foreach ($sales_return->saleReturnDetails as $returnDetail) {
    //                 // Debugging output
    //                 // dd([
    //                 //     'Product ID' => $returnDetail->productId,
    //                 //     'Returned Qty' => $returnDetail->rQty,
    //                 //     'Sales Point ID' => $data->salesPointId ?? 'N/A',
    //                 //     'Transaction ID' => $sales_return->id
    //                 // ]);

    //                 // Create Ledger Entry
    //                 $this->feedLedgerService->createFeedLedgerEntry(
    //                     sectorId: $data->salesPointId ?? null,
    //                     productId: $returnDetail->productId,
    //                     transactionId: $sales_return->saleReturnId,
    //                     trType: 'Sales Return',
    //                     date: now(),
    //                     qty: $returnDetail->rQty,
    //                     remarks: 'Feed Sales Return Adjustment'
    //                 );

    //                 // Store or Update Egg Stock (if applicable)
    //                 $this->feedStockService->FeedstoreOrUpdateStock(
    //                     sectorId: $data->salesPointId ?? null,
    //                     productId: $returnDetail->productId,
    //                     qty: $returnDetail->rQty,
    //                     trDate: now()
    //                 );
    //             }
    //         } else {

    //         }

    //         // Accounting Logic (unchanged)
    //         $voucherNo = $sales_return->saleReturnId;
    //         $voucherType = 'FeedSalesReturn';
    //         $voucherDate = $sales_return->returnDate;
    //         $companyId = $data->companyId;
    //         $returnHead = AccountLedgerName::where(['subGroupId' => '27', 'company_id' => $companyId])->first();
    //         $chartOfHeadId = $returnHead->id;
    //         $dealerHead = AccountLedgerName::where(['partyId' => $data->dealerId, 'partyType' => 'D'])->first();

    //         // Credit Logic
    //         $this->accountCredit->setCreditData(
    //             chartOfHeadId: $dealerHead->id,
    //             companyId: $companyId,
    //             voucherNo: $voucherNo,
    //             voucherType: $voucherType,
    //             voucherDate: $voucherDate,
    //             note: 'Feed Sales return approved credit entry',
    //             credit: $totalReturnAmount
    //         );

    //         // Debit Logic
    //         $this->accountDebit->setDebitData(
    //             chartOfHeadId: $chartOfHeadId,
    //             companyId: $companyId,
    //             voucherNo: $voucherNo,
    //             voucherType: $voucherType,
    //             voucherDate: $voucherDate,
    //             note: 'Feed Sales return approved debit entry',
    //             debit: $totalReturnAmount
    //         );

    //         // Update dealer and return ledger balances
    //         $dealerHead->update([
    //             'current_balance' => $dealerHead->current_balance - $totalReturnAmount
    //         ]);

    //         $returnHead->update([
    //             'current_balance' => $returnHead->current_balance + $totalReturnAmount
    //         ]);

    //         // Clear cache after updating status
    //         $this->cacheService->clearAllCache();

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Sales Return Status changed successfully, stock updated, and ledger entries created!',
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Failed to update Sales Return Status',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }


    public function statusUpdate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Validate the new status value
            $request->validate([
                'status' => 'required|string|in:approved,declined,pending',
            ]);

            // Find the sales return record
            $sales_return = FeedSalesReturn::with(['feed', 'saleReturnDetails'])->findOrFail($id);

            // Prevent any updates if the current status is already approved
            if ($sales_return->status === 'approved') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The status is already approved and cannot be changed.',
                ], 400);
            }

            // Check if the requested status is the same as the current status
            if ($sales_return->status === $request->status) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The status is already ' . $request->status . '. No changes were made.',
                ], 400);
            }
            // Debugging output
            // dd($sales_return->saleReturnDetails->toArray());

            $data = $sales_return->feed;

            // Update sales return status
            $sales_return->status = $request->status;
            $sales_return->appBy = auth()->id();
            $totalReturnAmount = $sales_return->totalReturnAmount;
            $sales_return->save();

            // Ensure salesReturnDetails exists before looping
            if ($sales_return->saleReturnDetails->isNotEmpty()) {
                foreach ($sales_return->saleReturnDetails as $returnDetail) {
                    // Debugging output
                    // dd([
                    //     'Product ID' => $returnDetail->productId,
                    //     'Returned Qty' => $returnDetail->rQty,
                    //     'Sales Point ID' => $data->salesPointId ?? 'N/A',
                    //     'Transaction ID' => $sales_return->id
                    // ]);

                    // Create Ledger Entry
                    $this->feedLedgerService->createFeedLedgerEntry(
                        sectorId: $data->salesPointId ?? null,
                        productId: $returnDetail->productId,
                        transactionId: $sales_return->saleReturnId,
                        trType: 'Sales Return',
                        date: now(),
                        qty: $returnDetail->rQty,
                        remarks: 'Feed Sales Return Adjustment'
                    );

                    // Store or Update Egg Stock (if applicable)
                    $this->feedStockService->FeedstoreOrUpdateStock(
                        sectorId: $data->salesPointId ?? null,
                        productId: $returnDetail->productId,
                        qty: $returnDetail->rQty,
                        trDate: now()
                    );


                }
            } else {

            }

            // Labour details only if isLabourBill == true
            if ($sales_return->isLabourBill) {
                // Find labourId by depotId (salesPointId)
                $labour = \App\Models\LabourInfo::where('depotId', $data->salesPointId)->first();
                $labourId = $labour?->id;

                if ($labourId && $sales_return->saleReturnDetails->isNotEmpty()) {
                    foreach ($sales_return->saleReturnDetails as $returnDetail) {
                        $this->labourDetailsAddService->addLabourDetail(
                            labourId: $labourId,
                            depotId: $data->salesPointId,
                            transactionId: $sales_return->saleReturnId,
                            transactionType: 'feedSalesReturn',
                            workType: 'Feed Return',
                            tDate: $sales_return->returnDate,
                            qty: -$returnDetail->rQty, // ❗ Negative because it's a return
                            status: 'approved'
                        );
                    }
                }
            }


            // Accounting Logic (unchanged)
            $voucherNo = $sales_return->saleReturnId;
            $voucherType = 'FeedSalesReturn';
            $voucherDate = $sales_return->returnDate;
            $companyId = $data->companyId;
            $returnHead = AccountLedgerName::where(['subGroupId' => '27', 'company_id' => $companyId])->first();
            $chartOfHeadId = $returnHead->id;
            $dealerHead = AccountLedgerName::where(['partyId' => $data->dealerId, 'partyType' => 'D'])->first();

            // Credit Logic
            $this->accountCredit->setCreditData(
                chartOfHeadId: $dealerHead->id,
                companyId: $companyId,
                voucherNo: $voucherNo,
                voucherType: $voucherType,
                voucherDate: $voucherDate,
                note: 'Feed Sales return approved credit entry',
                credit: $totalReturnAmount
            );

            // Debit Logic
            $this->accountDebit->setDebitData(
                chartOfHeadId: $chartOfHeadId,
                companyId: $companyId,
                voucherNo: $voucherNo,
                voucherType: $voucherType,
                voucherDate: $voucherDate,
                note: 'Feed Sales return approved debit entry',
                debit: $totalReturnAmount
            );

            // Update dealer and return ledger balances
            $dealerHead->update([
                'current_balance' => $dealerHead->current_balance - $totalReturnAmount
            ]);

            $returnHead->update([
                'current_balance' => $returnHead->current_balance + $totalReturnAmount
            ]);

            // Clear cache after updating status
            $this->cacheService->clearAllCache();

            DB::commit();

            return response()->json([
                'message' => 'Sales Return Status changed successfully, stock updated, and ledger entries created!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update Sales Return Status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $sales_return = FeedSalesReturn::find($id);
        if (!$sales_return) {
          return response()->json(['message' => 'Feed Sales Return not found'], 404);
        }
        $sales_return->delete();
        return response()->json([
          'message' => 'Feed Sales Return deleted successfully',
        ], 200);
    }
}