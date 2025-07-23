<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\LabourDetailRequest;
use App\Http\Resources\Feed\LabourDetailResource;
use App\Models\LabourDetail;
use Illuminate\Http\Request;
use App\Traits\SectorFilter;

class LabourDetailController extends Controller
{
    use SectorFilter;

    public function index(Request $request)
    {

         $oneYearAgo = now()->subYear()->format('Y-m-d');
         $today = today()->format('Y-m-d');

         $labourId = $request->labourId ?? null;
         $depotId = $request->depotId ?? null;
         $transactionId = $request->transactionId ?? null;
         $workType = $request->workType ?? null;
         $transactionType = $request->transactionType ?? null;
         $startDate = $request->input('startDate', $oneYearAgo);
         $endDate = $request->input('endDate', $today);
         $status = $request->status ?? null;

         $query = LabourDetail::query();
        // ✅ Sector-based filter
        $userId = auth()->id();
        $canPass = $this->adminFilter($userId);

        if (!$canPass) {
            $sectorIds = \App\Models\UserManagesSectors::where('userId', $userId)->pluck('sectorId')->toArray();

            if (!empty($sectorIds)) {
                $query->where(function ($q) use ($sectorIds) {
                    $q->whereIn('depotId', $sectorIds);
                });
            } else {
                return response()->json(['message' => 'No sector access assigned.'], 403);
            }
        }
        // Filter by labourId
        if ($labourId) {
            $query->where('labourId', $labourId);
        }

         // Filter by depotId
         if ($depotId) {
            $query->where('depotId', $depotId);
        }

         // Filter by transactionId
         if ($transactionId) {
            $query->where('transactionId', $transactionId);
        }

         // Filter by  workType
         if ($workType) {
            $query->where('workType', $workType);
        }

         // Filter by  transactionType
         if ($transactionType) {
            $query->where('transactionType', $transactionType);
        }

        // Filter tDate
        if ($startDate && $endDate) {
            $query->whereBetween('tDate', [$startDate, $endDate]);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

       // Fetch labourDetails with eager loading of related data
       $lab_details = $query->latest()->get();


        if ($lab_details->isEmpty()) {
            return response()->json([
                'message' => 'No Labour Detail found',
                'data' => []
            ], 200);
        }

        // Use the LabourDetailResource to transform the data
        $transformedLabourDetails = LabourDetailResource::collection($lab_details);

        // Return labourDetails transformed with the resource
        return response()->json([
        'message' => 'Success!',
        'data' => $transformedLabourDetails
        ], 200);

    }

    public function store(LabourDetailRequest $request)
    {
        try {

            $lab_detail = new LabourDetail();

            $lab_detail->labourId = $request->labourId;
            $lab_detail->depotId = $request->depotId;
            $lab_detail->unitId = 1;
            $lab_detail->transactionId = $request->transactionId;
            $lab_detail->transactionType = $request->transactionType;
            $lab_detail->workType = $request->workType;
            $lab_detail->tDate = $request->tDate;
            $lab_detail->qty = $request->qty;
            $lab_detail->bAmount = $request->bAmount;
            $lab_detail->crBy = auth()->id();
            $lab_detail->appBy = auth()->id();
            $lab_detail->status =  $request->status;
            // $lab_detail->status = 'pending';
            $lab_detail->save();
            //dd($lab_detail);

            return response()->json([
                'message' => 'Labour details created successfully',
                'data' => new LabourDetailResource($lab_detail),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        $lab_detail = LabourDetail::find($id);

        if (!$lab_detail) {
            return response()->json(['message' => 'Labour Details not found'], 404);
        }
        return new LabourDetailResource($lab_detail);
    }

    public function update(LabourDetailRequest $request, $id)
    {
        try {

            $lab_detail = LabourDetail::find($id);

            if (!$lab_detail) {
                return $this->sendError('Labour Detail not found.');
            }

            $lab_detail->labourId = $request->labourId;
            $lab_detail->depotId = $request->depotId;
            $lab_detail->unitId = 1;
            $lab_detail->transactionId = $request->transactionId;
            $lab_detail->transactionType = $request->transactionType;
            $lab_detail->workType = $request->workType;
            $lab_detail->tDate = $request->tDate;
            $lab_detail->qty = $request->qty;
            $lab_detail->bAmount = $request->bAmount;
            $lab_detail->status = 'pending';
            $lab_detail->update();

            return response()->json([
                'message' => 'Labour Detail Updated successfully',
                'data' => new LabourDetailResource($lab_detail),
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    public function statusUpdate(Request $request, $id)
    {
        $lab_detail = LabourDetail::find($id);

        if (!$lab_detail) {
            return response()->json([
                'message' => 'Labour detail not found'
            ], 404);
        }

        if ($lab_detail->payStatus === 'Paid') {
            return response()->json([
                'message' => 'Payment is already approved and cannot be changed.'
            ], 400);
        }

        $lab_detail->status = $request->status;
        $lab_detail->appBy = auth()->id();
        $lab_detail->update();

        return response()->json([
        'message' => 'Labour detail Status change successfully',
        ], 200);
    }

    public function destroy($id)
    {
        $lab_detail = LabourDetail::find($id);
        if (!$lab_detail) {
            return response()->json(['message' => 'Labour Detail not found'], 404);
        }

        $lab_detail->delete();
        return response()->json([
            'message' => 'Labour Detail deleted successfully',
        ], 200);
    }
}
