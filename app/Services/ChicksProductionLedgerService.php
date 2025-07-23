<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ChicksProductionLedgerService
{
    /**
     * Create a new ledger entry for a production transaction.
     *
     * @param int $hatcheryId
     * @param int $productId
     * @param string $transactionId
     * @param int $breedId
     * @param string $trType
     * @param string $date
     * @param string $approxQty
     * @param float $finalQty
     * @param string $batchNo
     * @param string|null $remarks
     * @return bool
     */
    public function createChicksLedgerEntry($hatcheryId, $productId, $transactionId, $breedId, $trType, $date, $approxQty, $finalQty, $batchNo, $remarks)
    {
        return DB::table('chicks_production_ledgers')->insert([
            'hatcheryId'    => $hatcheryId,
            'productId'     => $productId,
            'transactionId' => $transactionId,
            'breedId'     => $breedId,
            'trType'        => $trType,
            'date'          => $date,
            'approxQty'     => $approxQty,
            'finalQty'      => $finalQty,
            'batchNo'      => $batchNo,
            'remarks'       => $remarks,
            'appBy'         => auth()->id(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }


    public function createChicksStockAdjLedgerEntry(
        $hatcheryId,
        $productId,
        $transactionId, // e.g. "CAJ25070001"
        $breedId,
        $trType,
        $date,
        $qty,
        $batchNo,
        $remarks
    ) {
        return DB::table('chicks_production_ledgers')->insert([
            'hatcheryId'    => $hatcheryId,
            'productId'     => $productId,
            'transactionId' => $transactionId,
            'breedId'       => $breedId,
            'trType'        => $trType,
            'date'          => $date,
            'approxQty'     => $qty,
            'finalQty'      => 0,
            'batchNo'       => $batchNo,
            'remarks'       => $remarks,
            'appBy'         => auth()->id(),
            'created_at'    => now(),
            'updated_at'    => now()
        ]);
    }

    public function createChicksStockAdjLedgerEntryAdd(
        $hatcheryId,
        $productId,
        $transactionId, // e.g. "CAJ25070001"
        $breedId,
        $trType,
        $date,
        $qty,
        $batchNo,
        $remarks
    ) {
        return DB::table('chicks_production_ledgers')->insert([
            'hatcheryId'    => $hatcheryId,
            'productId'     => $productId,
            'transactionId' => $transactionId,
            'breedId'       => $breedId,
            'trType'        => $trType,
            'date'          => $date,
            'approxQty'     => $qty,
            'finalQty'      => 0,
            'batchNo'       => $batchNo,
            'remarks'       => $remarks,
            'appBy'         => auth()->id(),
            'created_at'    => now(),
            'updated_at'    => now()
        ]);
    }

}