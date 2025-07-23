<?php

namespace App\Http\Controllers\Api\Feed;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\LabourInfoRequest;
use App\Http\Resources\Feed\LabourInfoResource;
use App\Models\LabourInfo;
use Illuminate\Http\Request;
use App\Models\AccountLedgerName;
use App\Services\AddAccountLedgerService;
use Illuminate\Support\Facades\DB;

class LabourInfoController extends Controller
{
    protected $accountLedgerService;

    public function __construct(AddAccountLedgerService $accountLedgerService)
    {
        $this->accountLedgerService = $accountLedgerService;
    }
    public function index(Request $request)
    {
        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

        $concernPerson = $request->concernPerson ?? null;
        $depotId = $request->depotId ?? null;
        $paymentCycle = $request->paymentCycle ?? null;
        $startDate = $request->input('startDate', $oneYearAgo);
        $endDate = $request->input('endDate', $today);
        $status = $request->status ?? null;


      $query = LabourInfo::query();

      // Filter by concernPerson
      if ($concernPerson) {
        $query->where('concernPerson', 'LIKE', '%' . $concernPerson . '%');
      }

        // Filter by depotId
        if ($depotId) {
            $query->where('depotId', $depotId);
        }
      // Filter by paymentCycle
      if ($paymentCycle) {
        $query->where('paymentCycle', $paymentCycle);
      }

       // Filter contactDate
       if ($startDate && $endDate) {
        $query->whereBetween('contactDate', [$startDate, $endDate]);
    }

    // Filter by status
    if ($status) {
        $query->where('status', $status);
    }
      // Fetch labourInfo with eager loading of related data
      $lab_infos = $query->latest()->get();

      // Check if any labourInfo found
      if ($lab_infos->isEmpty()) {
        return response()->json(['message' => 'No Labour Info found', 'data' => []], 200);
      }

      // Use the LabourInfoResource to transform the data
      $transformedLabourInfos = LabourInfoResource::collection($lab_infos);

      // Return labourInfo transformed with the resource
      return response()->json([
        'message' => 'Success!',
        'data' => $transformedLabourInfos
      ], 200);
    }


    public function store(LabourInfoRequest $request)
    {
        try {
            $lab_info = new LabourInfo();

            $lab_info->labourName = $request->labourName;
            $lab_info->concernPerson = $request->concernPerson;
            $lab_info->contactNo = $request->contactNo;
            $lab_info->location = $request->location;
            $lab_info->depotId = $request->depotId;
            $lab_info->unitId = $request->unitId;
            $lab_info->contactDate = $request->contactDate;
            $lab_info->expDate = $request->expDate;
            $lab_info->fPrice = $request->fPrice;
            $lab_info->cPrice = $request->cPrice;
            $lab_info->oPrice = $request->oPrice;
            $lab_info->paymentCycle = $request->paymentCycle;
            $lab_info->paymentType = $request->paymentType;
            $lab_info->paymentInfo = $request->paymentInfo;
            $lab_info->note = $request->note;
            $lab_info->crBy = auth()->id();
            $lab_info->status = 'pending';
            $lab_info->save();
            return response()->json([
              'message' => 'Labour info created successfully',
              'data' => new LabourInfoResource($lab_info),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
      }


      public function show($id)
      {
        $lab_info = LabourInfo::find($id);

        if (!$lab_info) {
          return response()->json(['message' => 'Labour info not found'], 404);
        }
        return new LabourInfoResource($lab_info);
      }


      public function update(LabourInfoRequest $request, $id)
      {
          try {

              $lab_info = LabourInfo::find($id);

              if (!$lab_info) {
                return $this->sendError('Labour info not found.');
              }

              $lab_info->labourName = $request->labourName;
              $lab_info->concernPerson = $request->concernPerson;
              $lab_info->contactNo = $request->contactNo;
              $lab_info->location = $request->location;
              $lab_info->depotId = $request->depotId;
              $lab_info->unitId = $request->unitId;
              $lab_info->contactDate = $request->contactDate;
              $lab_info->expDate = $request->expDate;
              $lab_info->fPrice = $request->fPrice;
              $lab_info->cPrice = $request->cPrice;
              $lab_info->oPrice = $request->oPrice;
              $lab_info->paymentCycle = $request->paymentCycle;
              $lab_info->paymentType = $request->paymentType;
              $lab_info->paymentInfo = $request->paymentInfo;
              $lab_info->note = $request->note;
              $lab_info->status = 'pending';

              $lab_info->update();


              return response()->json([
                'message' => 'labour info Maps Updated successfully',
                'data' => new LabourInfoResource($lab_info),
              ], 200);
            } catch (\Exception $e) {
              // Handle the exception here
              return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
            }
      }

      public function statusUpdateold(Request $request, $id)
        {
            $lab_info = LabourInfo::find($id);
            $lab_info->status = $request->status;
            $lab_info->appBy = auth()->id();
            $lab_info->update();

            return response()->json([
            'message' => 'Labour info Status change successfully',
            ], 200);
        }

    public function statusUpdate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $lab_info = LabourInfo::find($id);

            if (!$lab_info) {
                return response()->json(['message' => 'Labour Info not found'], 404);
            }

            $lab_info->status = $request->status;
            $lab_info->appBy = auth()->id();
            $lab_info->update();

            if ($lab_info->status === 'approved') {
                $existingLedger = AccountLedgerName::where('partyId', $lab_info->id)
                    ->where('partyType', 'L')
                    ->first();

                if (!$existingLedger) {
                    $this->accountLedgerService->addAccountLedger(
                        $lab_info->labourName,
                        $lab_info->depotId,
                        9,
                        29,
                        'Depot Labour Bill Account',
                        null,
                        5,
                    'Debit',
                        0,
                        0,
                        true,
                        true,
                        $lab_info->id,
                         'L'
                    );
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Labour Info Status changed successfully',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

      public function destroy($id)
      {
        $lab_info = LabourInfo::find($id);
        if (!$lab_info) {
          return response()->json(['message' => 'Labour Info not found'], 404);
        }
        $lab_info->delete();
        return response()->json([
          'message' => 'Labour Info deleted successfully',
        ], 200);
      }


      public function getLabInfoList()
      {
        $labInfoList = LabourInfo::where('status', 'approved')
          ->with('depot:id,name')
          ->select('id', 'labourName', 'concernPerson','depotId')
          ->get();

        $labInfoList->transform(function ($labInfo) {
          $labInfo->name = $labInfo->depot->name ?? null;
          unset($labInfo->zoneId);
          return $labInfo;
        });

        return response()->json([
          'data' => $labInfoList
        ], 200);
      }
}
