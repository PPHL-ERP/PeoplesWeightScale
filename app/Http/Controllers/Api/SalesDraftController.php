<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesDraftRequest;
use App\Http\Resources\SalesDraftResource;
use App\Http\Resources\SalesOrderResource;
use App\Models\SalesDraft;
use App\Models\SalesDraftDetails;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SalesDraftController extends Controller
{

    public function index()
    {
        $sales_drafts = SalesDraft::latest()->get();

        if ($sales_drafts->isEmpty()) {
          return response()->json(['message' => 'No Sales Drafts found'], 200);
        }
        return SalesDraftResource::collection($sales_drafts);
    }

    public function store(SalesDraftRequest $request)
    {
        try {


            DB::beginTransaction();

            $sales_draft = new SalesDraft();
            $sales_draft->saleId = $request->saleId;
            $sales_draft->bookingId = $request->bookingId;
            $sales_draft->saleCategoryId = $request->saleCategoryId;
            $sales_draft->dealerId = $request->dealerId;
            $sales_draft->salesPointId = $request->salesPointId;
            $sales_draft->companyId = $request->companyId;
            $sales_draft->saleType = $request->saleType;
            $sales_draft->salesPerson = $request->salesPerson;
            $sales_draft->transportType = $request->transportType;
            $sales_draft->outTransportInfo = json_encode($request->outTransportInfo);
            $sales_draft->dueAmount = $request->dueAmount;
            $sales_draft->totalAmount = $request->totalAmount;
            $sales_draft->discount = $request->discount;
            $sales_draft->discountType = $request->discountType;
            $sales_draft->fDiscount = $request->fDiscount;
            $sales_draft->vat = $request->vat;
            $sales_draft->invoiceDate = $request->invoiceDate;
            $sales_draft->note = $request->note;
            $sales_draft->pOverRideBy = $request->pOverRideBy;
            $sales_draft->transportCost = $request->transportCost;
            $sales_draft->othersCost = json_encode($request->othersCost);
            $sales_draft->dueDate = $request->dueDate;
            $sales_draft->depotCost = $request->depotCost;
            $sales_draft->chartOfHeadId = $request->chartOfHeadId;
            $sales_draft->paymentStatus = 'DUE';
            $sales_draft->billingAddress = $request->billingAddress;
            $sales_draft->deliveryAddress = $request->deliveryAddress;
            $sales_draft->crBy = auth()->id();
            $sales_draft->status = 'pending';

            $sales_draft->save();

            //dd($sales_draft);


            // Detail input START
            $saleId = $sales_draft->id;

            foreach ($request->input('sales_draft_details', []) as $detail) {
              $productId = $detail['productId'];
              $tradePrice = $detail['tradePrice'];
              $salePrice = $detail['salePrice'];
              $qty = $detail['qty'];
              $unitId = $detail['unitId'];
              $unitBatchNo = $detail['unitBatchNo'];

              $dbDetail = new SalesDraftDetails();
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
              'message' => 'Sales Draft created successfully',
              'data' => new SalesDraftResource($sales_draft),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
    }

    public function show($id)
    {
        $sales_draft = SalesDraft::find($id);
        if (!$sales_draft) {
          return response()->json(['message' => 'Sales Draft not found'], 404);
        }
        return new SalesDraftResource($sales_draft);
    }

    public function update(SalesDraftRequest $request, $id)
    {
        try {

            $sales_draft = SalesDraft::find($id);

            if (!$sales_draft) {
              return $this->sendError('Sales Draft not found.');
            }

            // Update the main SalesOrder fields
            $sales_draft->bookingId = $request->bookingId;
            $sales_draft->saleCategoryId = $request->saleCategoryId;
            $sales_draft->dealerId = $request->dealerId;
            $sales_draft->salesPointId = $request->salesPointId;
            $sales_draft->companyId = $request->companyId;
            $sales_draft->saleType = $request->saleType;
            $sales_draft->salesPerson = $request->salesPerson;
            $sales_draft->transportType = $request->transportType;
            $sales_draft->outTransportInfo = json_encode($request->outTransportInfo);
            $sales_draft->dueAmount = $request->dueAmount;
            $sales_draft->totalAmount = $request->totalAmount;
            $sales_draft->discount = $request->discount;
            $sales_draft->discountType = $request->discountType;
            $sales_draft->fDiscount = $request->fDiscount;
            $sales_draft->vat = $request->vat;
            $sales_draft->invoiceDate = $request->invoiceDate;
            $sales_draft->note = $request->note;
            $sales_draft->pOverRideBy = $request->pOverRideBy;
            $sales_draft->transportCost = $request->transportCost;
            $sales_draft->othersCost = json_encode($request->othersCost);
            $sales_draft->dueDate = $request->dueDate;
            $sales_draft->depotCost = $request->depotCost;
            $sales_draft->chartOfHeadId = $request->chartOfHeadId;
            $sales_draft->paymentStatus = $request->paymentStatus;
            $sales_draft->billingAddress = $request->billingAddress;
            $sales_draft->deliveryAddress = $request->deliveryAddress;
            $sales_draft->status = $request->status;


            $sales_draft->save();

            // Update SalesDraftDetails
            $existingDetailIds = $sales_draft->details()->pluck('id')->toArray();

            foreach ($request->input('sales_draft_details', []) as $detail) {
                if (isset($detail['id']) && in_array($detail['id'], $existingDetailIds)) {
                    // Update existing detail
                    $sbDetail = SalesDraftDetails::find($detail['id']);
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
                    $sbDetail = new SalesDraftDetails();
                    $sbDetail->saleId = $sales_draft->id;
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
            SalesDraftDetails::whereIn('id', $existingDetailIds)->delete();

            return response()->json([
                'message' => 'Sales Draft updated successfully',
                'data' => new SalesDraftResource($sales_draft),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function statusUpdate(Request $request, $id)
    {
      $sales_draft = SalesDraft::find($id);
      $sales_draft->status = $request->status;
      $sales_draft->appBy = auth()->id();

      $sales_draft->update();
      return response()->json([
        'message' => 'Sales Draft Status change successfully',
      ], 200);
    }
 public function destroy($id)
    {
        $sales_draft = SalesDraft::find($id);
        if (!$sales_draft) {
          return response()->json(['message' => 'Sales Draft not found'], 404);
        }
        $sales_draft->delete();
        return response()->json([
          'message' => 'Sales Draft deleted successfully',
        ], 200);
    }
}