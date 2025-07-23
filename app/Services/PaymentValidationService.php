<?php
namespace App\Services;

use App\Models\LabourInfo;
use App\Models\LabourPayment;

class PaymentValidationService
{
  public function validateCreation($dateFrom, $dateTo, $labourIds)
  {
    $messages = [];

    $labours = LabourInfo::query()->whereIn('id', $labourIds)->get();

    foreach ($labours as $labour) {
      $labourId = $labour->id;
      $labourName = $labour->labourName;

      $ledger = LabourPayment::with(['labours'])
        ->where('billStartDate', $dateFrom)
        ->whereHas('labours', function ($query) use ($labourId) {
          $query->where('labourId', $labourId);
        })
        ->first();

      if ($ledger) {
        $billEndDate = $ledger->billEndDate;

        $messages[] = [
          'dateFrom' => $dateFrom,
          'dateTo' => $billEndDate,
          'labourId' => $labourId,
          'labourName' => $labourName,
        ];
      }
    }

    return $messages;
  }
}