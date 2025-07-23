<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\FeedDeliveryRequest;
use App\Http\Resources\Feed\FeedDeliveryResource;
use App\Models\FeedDelivery;
use App\Models\FeedDeliveryDetail;
use App\Models\Product;
use Illuminate\Http\Request;

class FeedDeliveryController extends Controller
{

    public function index(Request $request)
    {

         $oneYearAgo = now()->subYear()->format('Y-m-d');
         $today = today()->format('Y-m-d');

         $feedId = $request->feedId ?? null;
         $dealerId = $request->dealerId ?? null;
         $productId = $request->productId ?? null;
         $childCategoryId = $request->childCategoryId ?? null;
         $startDate = $request->input('startDate', $oneYearAgo);
         $endDate = $request->input('endDate', $today);

         $status = $request->status ?? null;

         $query = FeedDelivery::query();

        // Filter by feedId
        if ($feedId) {
            $query->where('feedId', $feedId);
        }

         // Filter by dealerId
         if ($dealerId) {
            $query->where('dealerId', $dealerId);
        }

         // Filter by childCategoryId
         if ($childCategoryId) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('productId', $productIds);
        }

        // Filter by productId
        if ($productId) {
            $query->where('productId', $productId);
        }

        // Filter Date
        if ($startDate && $endDate) {
            $query->whereBetween('deliveryDate', [$startDate, $endDate]);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

       // $fDelivery = $query->latest()->get();
        $fDelivery = $query->with(['product', 'childCategory'])->latest()->get();


        if ($fDelivery->isEmpty()) {
            return response()->json([
                'message' => 'Feed Delivery No Data found',
                'data' => []
            ], 200);
        }

        return FeedDeliveryResource::collection($fDelivery);

    }


    public function store(Request $request)
    {
        //
    }


    public function show($id)
    {
        $feed_delivery = FeedDelivery::find($id);
        if (!$feed_delivery) {
            return response()->json(['message' => 'Feed Delivery not found'], 404);
        }
        return new FeedDeliveryResource($feed_delivery);
    }


    public function update(FeedDeliveryRequest $request,  $id)
    {
        try {
            $feed_delivery = FeedDelivery::find($id);

            if (!$feed_delivery) {
                return $this->sendError('Feed Delivery not found.');
            }

            // Check if the FeedDelivery is approved
            if ($feed_delivery->status === 'approved') {
                return response()->json(['message' => 'Cannot modify  for approved Feed Delivery.'], 403);
            }

            // Update the main FeedDElivery fields
            $feed_delivery->feedId = $request->feedId;
            $feed_delivery->dealerId = $request->dealerId;
            $feed_delivery->salesPerson = $request->salesPerson;
            $feed_delivery->deliveryPointDetails = $request->deliveryPointDetails;
            $feed_delivery->deliveryPersonDetails = json_encode($request->deliveryPersonDetails);
            $feed_delivery->deliveryDate = $request->deliveryDate;
            $feed_delivery->transportType = $request->transportType;
            $feed_delivery->roadInfo = $request->roadInfo;
            $feed_delivery->driverName = $request->driverName;
            $feed_delivery->mobile = $request->mobile;
            $feed_delivery->vehicleNo = $request->vehicleNo;
            $feed_delivery->note = $request->note;
            $feed_delivery->status = 'pending';

            $feed_delivery->save();

            // Update FeedOrderDetails
            $existingDetailIds = $feed_delivery->details()->pluck('id')->toArray();

            foreach ($request->input('delivery_details', []) as $detail) {
                if (isset($detail['id']) && in_array($detail['id'], $existingDetailIds)) {
                    // Update existing detail
                    $sbDetail = FeedDeliveryDetail::find($detail['id']);
                    $sbDetail->productId = $detail['productId'];
                    $sbDetail->qty = $detail['qty'];
                    $sbDetail->dQty = $detail['dQty'];
                    $sbDetail->note = $detail['note'];
                    $sbDetail->save();

                    // Remove updated detail ID from the list of existing IDs
                    $existingDetailIds = array_diff($existingDetailIds, [$sbDetail->id]);
                } else {
                    // Create new detail if not exists
                    $sbDetail = new FeedDeliveryDetail();
                    $sbDetail->feedId = $feed_delivery->id;
                    $sbDetail->productId = $detail['productId'];
                    $sbDetail->qty = $detail['qty'];
                    $sbDetail->dQty = $detail['dQty'];
                    $sbDetail->note = $detail['note'];
                    $sbDetail->save();
                }
            }
            // Delete removed details
            FeedDeliveryDetail::whereIn('id', $existingDetailIds)->delete();

            return response()->json([
                'message' => 'Feed Delivery updated successfully',
                'data' => new FeedDeliveryResource($feed_delivery),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
     }



    public function destroy(string $id)
    {
        //
    }
}