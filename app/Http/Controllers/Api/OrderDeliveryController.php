<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OrderDeliveryService;
use App\Http\Resources\OrderDeliveryResource;
use App\Models\OrderDeliveryDetails;

class OrderDeliveryController extends Controller
{
    protected $orderDeliveryService;

    public function __construct(OrderDeliveryService $orderDeliveryService)
    {
        $this->orderDeliveryService = $orderDeliveryService;
    }

    /**
     * Display a listing of order delivery records with optional filters.
     *
     * Optional filters: saleId, dealerCode, driverName, driverPhone, vehicleNo, vehicleType, status.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $data = $this->orderDeliveryService->getData($request);

        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'No Order Delivery records found',
                'data' => []
            ], 200);
        }

        return response()->json([
            'message' => 'Success!',
            'data' => OrderDeliveryResource::collection($data)
        ], 200);
    }

    /**
     * Store new order delivery records from the frontend payload.
     *
     * Expected payload: a JSON array of order delivery objects.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // You may add request validation here if needed.
        $records = $this->orderDeliveryService->storeData($request);

        return response()->json([
            'message' => 'Order Delivery records created successfully',
            'data' => OrderDeliveryResource::collection($records)
        ], 200);
    }

    /**
     * Update the status of a specific order delivery record.
     *
     * Expected request data:
     * {
     *    "status": "approved"  // Allowed values: pending, approved, declined, delivered
     * }
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $record = $this->orderDeliveryService->updateStatus($request, $id);

        return response()->json([
            'message' => 'Order Delivery record status updated successfully',
            'data' => new OrderDeliveryResource($record)
        ], 200);
    }

    /**
     * Display the specified order delivery record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|OrderDeliveryResource
     */
    public function show($id)
    {
        $record = OrderDeliveryDetails::find($id);
        if (!$record) {
            return response()->json([
                'message' => 'Order Delivery record not found'
            ], 404);
        }
        return new OrderDeliveryResource($record);
    }

    /**
     * Remove the specified order delivery record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $record = OrderDeliveryDetails::find($id);
        if (!$record) {
            return response()->json([
                'message' => 'Order Delivery record not found'
            ], 404);
        }
        $record->delete();
        return response()->json([
            'message' => 'Order Delivery record deleted successfully'
        ], 200);
    }
}
