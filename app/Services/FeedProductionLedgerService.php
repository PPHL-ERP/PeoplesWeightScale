<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\FeedFarmProduction;

class FeedProductionLedgerService
{
    /**
     * Create a new ledger entry for a production transaction.
     *
     * @param int $sectorId
     * @param int $productId
     * @param int $transactionId
     * @param string $trType
     * @param string $date
     * @param float $qty
     * @param string $remarks
     * @return bool
     */
    public function createFeedLedgerEntry($sectorId, $productId, $transactionId, $trType, $date, $qty, $remarks)
    {
        // Fetch the last closing balance (previous row's closing balance)
        $lastClosingBalance = DB::table('feed_production_ledgers')
            ->where('sectorId', $sectorId)
            ->where('productId', $productId)
            ->where('status', 'approved')
            ->orderBy('id', 'desc')  // Get the last entry
            ->value('closingBalance');

        // If no previous closing balance exists, start with 0
        if ($lastClosingBalance === null) {
            $lastClosingBalance = 0;
        }

        // Calculate the new closing balance by adding the current qty to the last closing balance
        $newClosingBalance = $lastClosingBalance + $qty;

        // Insert the new data into the feed_production_ledgers table
        return DB::table('feed_production_ledgers')->insert([
            'sectorId' => $sectorId,
            'productId' => $productId,
            'transactionId' => $transactionId,  // Reference the production record
            'trType' => $trType,  // Transaction type
            'date' => $date,
            'qty' => $qty,
            'closingBalance' => $newClosingBalance,  // Updated cumulative balance after qty adjustment
            'remarks' => $remarks,
            'appBy' => auth()->id(),  // Authenticated user's ID
            'status' => 'approved',  // Set the status to approved
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function createFeedTransferLedgerEntry($sectorId, $productId, $transactionId, $trType, $date, $qty, $remarks)
    {
        return DB::table('feed_production_ledgers')->insert([
            'sectorId' => $sectorId,
            'productId' => $productId,
            'transactionId' => $transactionId,  // Reference the production record
            'trType' => $trType,  // Transaction type
            'date' => $date,
            'qty' => -$qty,
            'lockQty' => $qty,
            'remarks' => $remarks,
            'appBy' => auth()->id(),  // Authenticated user's ID
            'status' => 'pending',  // Set the status to pending initially
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    public function createFeedStockAdjLedgerEntry($sectorId, $productId, $transactionId, $trType, $date, $qty, $remarks)
    {
        return DB::table('feed_production_ledgers')->insert([
            'sectorId' => $sectorId,
            'productId' => $productId,
            'transactionId' => $transactionId,  // Reference the production record
            'trType' => $trType,  // Transaction type
            'date' => $date,
            'qty' => $qty,
            'lockQty' => 0,
            'remarks' => $remarks,
            'appBy' => auth()->id(),  // Authenticated user's ID
            'status' => 'approved',  // Set the status to pending initially
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    public function createFeedStockAdjLedgerEntryAdd($sectorId, $productId, $transactionId, $trType, $date, $qty, $remarks)
    {
        return DB::table('feed_production_ledgers')->insert([
            'sectorId' => $sectorId,
            'productId' => $productId,
            'transactionId' => $transactionId,  // Reference the production record
            'trType' => $trType,  // Transaction type
            'date' => $date,
            'qty' => $qty,
            'lockQty' => 0,
            'remarks' => $remarks,
            'appBy' => auth()->id(),  // Authenticated user's ID
            'status' => 'approved',  // Set the status to pending initially
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    public function createFeedReceiveLedgerEntry($sectorId, $productId, $transactionId, $trType, $date, $trqty, $remarks)
    {

        $lastClosingBalance = DB::table('feed_production_ledgers')
            ->where('sectorId', $sectorId)
            ->where('productId', $productId)
            ->where('status', 'approved')
            ->orderBy('id', 'desc')  // Get the last entry
            ->value('closingBalance');

        // If no previous closing balance exists, start with 0
        if ($lastClosingBalance === null) {
            $lastClosingBalance = 0;
        }

        // Calculate the new closing balance by adding the current qty to the last closing balance
        $newClosingBalance = $lastClosingBalance + $trqty;
                // dd($newClosingBalance);

        return DB::table('feed_production_ledgers')->insert([
            'sectorId' => $sectorId,
            'productId' => $productId,
            'transactionId' => $transactionId,  // Reference the production record
            'trType' => $trType,  // Transaction type
            'date' => $date,
            'qty' => $trqty,
            'closingBalance' => $newClosingBalance,  // Updated cumulative balance after qty adjustment

            'remarks' => $remarks,
            'appBy' => auth()->id(),  // Authenticated user's ID
            'status' => 'approved',  // Set the status to pending initially
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    // public function calculateNewFlockTotal($production)
    // {
    //     $previousFlockTotal = FeedFarmProduction::where('sectorId', $production->sectorId)
    //         ->where('productId', $production->productId)
    //         ->where('flockId', $production->flockId)
    //         ->where('status', 'approved')
    //         ->sum('qty');

    //     return $previousFlockTotal + $production->qty;
    // }


    public function getFeedTotalClosingBalance($sectorId, $productId)
    {
        // Get the latest closing balance from feed_stocks based on the most recent trDate
        $lastClosingBalance = DB::table('feed_stocks')
            ->where('sectorId', $sectorId)
            ->where('productId', $productId)
            ->orderBy('trDate', 'desc') // Get the most recent date
            ->value('closing');

        // Sum the lockQty from feed_production_ledgers based on conditions
        $totalLockQty = DB::table('feed_production_ledgers')
            ->where('sectorId', $sectorId)
            ->where('productId', $productId)
            ->where('trType', 'productionTransfer')
            ->where('status', 'pending')
            ->sum('lockQty');

        // If no closing balance exists, default it to 0
        $closingBalance = $lastClosingBalance ?? 0;

        return [
            'closingBalance' => $closingBalance,
            'lockQty' => $totalLockQty
        ];
    }



    public function lockQty($sectorId, $productId, $qty)
    {
        // Assuming you have a field `lockedQty` in your feed_production_ledgers or a related table
        DB::table('feed_production_ledgers')
            ->where('sectorId', $sectorId)
            ->where('productId', $productId)
            ->orderBy('id', 'desc')  // Get the last entry
            ->increment('lockQty', $qty);  // Add the locked quantity
    }
}