<?php
namespace App\Services;

use App\Models\LabourDetail;
use App\Models\LabourInfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LabourDetailsAddService
{
    // public function addLabourDetail($labourId, $depotId, $unitId, $transactionId, $transactionType, $workType, $tDate, $qty, $bAmount,$status)
    // {
    //     LabourDetail::create([
    //         'labourId' => $labourId,
    //         'depotId' => $depotId,
    //         'unitId' => $unitId,
    //         'transactionId' => $transactionId,
    //         'transactionType' => $transactionType,
    //         'workType' => $workType,
    //         'tDate' => $tDate,
    //         'qty' => $qty,
    //         'bAmount' => $bAmount,
    //         'status' => $status,
    //         'crBy' => Auth::id(),
    //         'appBy' => Auth::id(),

    //     ]);
    // }

    // with fPrice calculation
    public function addLabourDetail($labourId, $depotId,  $transactionId, $transactionType, $workType, $tDate, $qty, $status)
    {

        $labourInfo = LabourInfo::where('id', $labourId)->first();
        $bAmount = $labourInfo ? ($labourInfo->fPrice * $qty) : 0;

        LabourDetail::create([
            'labourId' => $labourId,
            'depotId' => $depotId,
            //'unitId' => $unitId,
            'unitId' => 1,
            'transactionId' => $transactionId,
            'transactionType' => $transactionType,
            'workType' => $workType,
            'tDate' => $tDate,
            'qty' => $qty,
            'bAmount' => $bAmount,
            'status' => $status,
            'crBy' => Auth::id(),
            'appBy' => Auth::id(),
        ]);
    }

  // Decline the associated LabourDetails records
    public function updateLabourDetailStatus($transactionId, $status)
{
    DB::table('labour_details')
        ->where('transactionId', $transactionId)
        ->update([
            'status' => $status,
            'updated_at' => now()
        ]);
}

}