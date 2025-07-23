<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\FeedDraftRequest;
use App\Http\Resources\Feed\FeedDraftResource;
use App\Models\FeedDraft;
use App\Models\FeedDraftDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedDraftController extends Controller
{

    public function index()
    {
        $feed_drafts = FeedDraft::latest()->get();

        if ($feed_drafts->isEmpty()) {
          return response()->json(['message' => 'No Feed Drafts found'], 200);
        }
        return FeedDraftResource::collection($feed_drafts);
    }


    public function store(FeedDraftRequest $request)
    {
        try {


            DB::beginTransaction();

            $feed_draft = new FeedDraft();
            $feed_draft->feedId = $request->feedId;
            $feed_draft->bookingId = $request->bookingId;
            $feed_draft->saleCategoryId = $request->saleCategoryId;
            $feed_draft->subCategoryId = $request->subCategoryId;
            $feed_draft->childCategoryId = $request->childCategoryId;
            $feed_draft->dealerId = $request->dealerId;
            $feed_draft->salesPointId = $request->salesPointId;
            $feed_draft->companyId = $request->companyId;
            $feed_draft->saleType = $request->saleType;
            $feed_draft->salesPerson = $request->salesPerson;
            $feed_draft->commissionId = $request->commissionId;
            $feed_draft->transportType = $request->transportType;
            $feed_draft->loadBy = $request->loadBy;
            $feed_draft->transportBy = $request->transportBy;
            $feed_draft->outTransportInfo = json_encode($request->outTransportInfo);
            $feed_draft->subTotal = $request->subTotal;
            $feed_draft->dueAmount = $request->dueAmount;
            $feed_draft->totalAmount = $request->totalAmount;
            $feed_draft->discount = $request->discount;
            $feed_draft->discountType = $request->discountType;
            $feed_draft->fDiscount = $request->fDiscount;
            $feed_draft->vat = $request->vat;
            $feed_draft->invoiceDate = $request->invoiceDate;
            $feed_draft->note = $request->note;
            $feed_draft->pOverRideBy = $request->pOverRideBy;
            $feed_draft->transportCost = $request->transportCost;
            $feed_draft->othersCost = json_encode($request->othersCost);
            $feed_draft->dueDate = $request->dueDate;
            $feed_draft->depotCost = $request->depotCost;
            $feed_draft->chartOfHeadId = $request->chartOfHeadId;
            $feed_draft->paymentStatus = 'DUE';
            $feed_draft->billingAddress = $request->billingAddress;
            $feed_draft->deliveryAddress = $request->deliveryAddress;
            $feed_draft->crBy = auth()->id();
            $feed_draft->status = 'pending';

            $feed_draft->save();

            //dd($feed_draft);


            // Detail input START
            $feedId = $feed_draft->id;

            foreach ($request->input('feed_draft_details', []) as $detail) {
              $productId = $detail['productId'];
              $tradePrice = $detail['tradePrice'];
              $salePrice = $detail['salePrice'];
              $qty = $detail['qty'];
              $unitId = $detail['unitId'];
              $unitBatchNo = $detail['unitBatchNo'];

              $dbDetail = new FeedDraftDetails();
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
              'message' => 'Feed Draft created successfully',
              'data' => new FeedDraftResource($feed_draft),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
    }


    public function show($id)
    {
        $feed_draft = FeedDraft::find($id);
        if (!$feed_draft) {
          return response()->json(['message' => 'Feed Draft not found'], 404);
        }
        return new FeedDraftResource($feed_draft);
    }


    public function update(FeedDraftRequest $request, string $id)
    {
        try {

            $feed_draft = FeedDraft::find($id);

            if (!$feed_draft) {
              return $this->sendError('Feed Draft not found.');
            }

            // Update the main SalesOrder fields
            $feed_draft->bookingId = $request->bookingId;
            $feed_draft->saleCategoryId = $request->saleCategoryId;
            $feed_draft->subCategoryId = $request->subCategoryId;
            $feed_draft->childCategoryId = $request->childCategoryId;
            $feed_draft->dealerId = $request->dealerId;
            $feed_draft->salesPointId = $request->salesPointId;
            $feed_draft->companyId = $request->companyId;
            $feed_draft->saleType = $request->saleType;
            $feed_draft->salesPerson = $request->salesPerson;
            $feed_draft->commissionId = $request->commissionId;
            $feed_draft->transportType = $request->transportType;
            $feed_draft->loadBy = $request->loadBy;
            $feed_draft->transportBy = $request->transportBy;
            $feed_draft->outTransportInfo = json_encode($request->outTransportInfo);
            $feed_draft->subTotal = $request->subTotal;
            $feed_draft->dueAmount = $request->dueAmount;
            $feed_draft->totalAmount = $request->totalAmount;
            $feed_draft->discount = $request->discount;
            $feed_draft->discountType = $request->discountType;
            $feed_draft->fDiscount = $request->fDiscount;
            $feed_draft->vat = $request->vat;
            $feed_draft->invoiceDate = $request->invoiceDate;
            $feed_draft->note = $request->note;
            $feed_draft->pOverRideBy = $request->pOverRideBy;
            $feed_draft->transportCost = $request->transportCost;
            $feed_draft->othersCost = json_encode($request->othersCost);
            $feed_draft->dueDate = $request->dueDate;
            $feed_draft->depotCost = $request->depotCost;
            $feed_draft->chartOfHeadId = $request->chartOfHeadId;
            $feed_draft->paymentStatus = $request->paymentStatus;
            $feed_draft->billingAddress = $request->billingAddress;
            $feed_draft->deliveryAddress = $request->deliveryAddress;
            $feed_draft->status = $request->status;


            $feed_draft->save();

            // Update FeedDraftDetails
            $existingDetailIds = $feed_draft->details()->pluck('id')->toArray();

            foreach ($request->input('feed_draft_details', []) as $detail) {
                if (isset($detail['id']) && in_array($detail['id'], $existingDetailIds)) {
                    // Update existing detail
                    $sbDetail = FeedDraftDetails::find($detail['id']);
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
                    $sbDetail = new FeedDraftDetails();
                    $sbDetail->feedId = $feed_draft->id;
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
            FeedDraftDetails::whereIn('id', $existingDetailIds)->delete();

            return response()->json([
                'message' => 'Feed Draft updated successfully',
                'data' => new FeedDraftResource($feed_draft),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function statusUpdate(Request $request, $id)
    {
      $feed_draft = FeedDraft::find($id);
      $feed_draft->status = $request->status;
      $feed_draft->appBy = auth()->id();

      $feed_draft->update();
      return response()->json([
        'message' => 'Feed Draft Status change successfully',
      ], 200);
    }

    public function destroy($id)
    {
        $feed_draft = FeedDraft::find($id);
        if (!$feed_draft) {
          return response()->json(['message' => 'Feed Draft not found'], 404);
        }
        $feed_draft->delete();
        return response()->json([
          'message' => 'Feed Draft deleted successfully',
        ], 200);
    }
}