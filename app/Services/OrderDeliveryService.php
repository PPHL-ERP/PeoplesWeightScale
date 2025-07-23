<?php

namespace App\Services;

use App\Models\OrderDeliveryDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderDeliveryService
{
    /**
     * Store order delivery records from the provided payload.
     *
     * Expected payload structure (JSON):
     * [
     *     {
     *         "saleId": "ESO25020001",
     *         "productId": 17,
     *         "qty": 100,
     *         "dealerCode": "DLR250018",
     *         "driverName": "John Doe",
     *         "driverPhone": "01712345678",
     *         "vehicleNo": "ABC-123",
     *         "vehicleType": "Truck",
     *         "status": "pending"
     *     },
     *     ...
     * ]
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function storeData(Request $request)
    {
        // Assume the payload is provided as a JSON array under the 'data' key.
        $payload = $request->input('data', []);

        return DB::transaction(function () use ($payload) {
            $records = [];
            foreach ($payload as $data) {
                $records[] = OrderDeliveryDetails::create([
                    //'sale_id'      => $data['saleId'] ?? null,
                    'sale_id'      => $data['feedId'] ?? null,
                    'product_id'   => $data['productId'] ?? null,
                    'qty'          => $data['qty'] ?? 0,
                    'dealer_code'  => $data['dealerCode'] ?? null,
                    'driver_name'  => $data['driverName'] ?? null,
                    'driver_phone' => $data['driverPhone'] ?? null,
                    'vehicle_no'   => $data['vehicleNo'] ?? null,
                    'vehicle_type' => $data['vehicleType'] ?? null,
                    'status'       => $data['status'] ?? 'pending',
                ]);
            }
            return collect($records);
        });
    }

    /**
     * Retrieve order delivery records with optional filters.
     *
     * Optional filters: saleId, dealerCode, driverName, driverPhone, vehicleNo, vehicleType, status.
     * These filters can be used individually, together, or omitted entirely.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getData(Request $request)
    {
        $query = OrderDeliveryDetails::query();

        // if ($request->filled('saleId')) {
        //     $query->where('sale_id', 'LIKE', '%' . $request->saleId . '%');
        // }
        if ($request->filled('feedId')) {
            $query->where('sale_id', 'LIKE', '%' . $request->feedId . '%');
        }
        if ($request->filled('dealerCode')) {
            $query->where('dealer_code', 'LIKE', '%' . $request->dealerCode . '%');
        }
        if ($request->filled('driverName')) {
            $query->where('driver_name', 'LIKE', '%' . $request->driverName . '%');
        }
        if ($request->filled('driverPhone')) {
            $query->where('driver_phone', 'LIKE', '%' . $request->driverPhone . '%');
        }
        if ($request->filled('vehicleNo')) {
            $query->where('vehicle_no', 'LIKE', '%' . $request->vehicleNo . '%');
        }
        if ($request->filled('vehicleType')) {
            $query->where('vehicle_type', 'LIKE', '%' . $request->vehicleType . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 100);
        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Update the status of an order delivery record.
     *
     * Expected request data:
     * {
     *    "status": "approved" // Allowed values: pending, approved, declined, delivered
     * }
     *
     * @param Request $request
     * @param int $id
     * @return OrderDeliveryDetails|null
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,approved,declined,delivered',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $record = OrderDeliveryDetails::find($id);
            if (!$record) {
                throw new \Exception("Order delivery record not found.");
            }
            $record->update([
                'status' => $request->status,
            ]);
            return $record;
        });
    }
}