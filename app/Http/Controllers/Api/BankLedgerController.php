<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankLedgerRequest;
use App\Http\Resources\BankLedgerResource;
use App\Models\BankLedger;
use Illuminate\Http\Request;

class BankLedgerController extends Controller
{
    public function index(Request $request)
    {
        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

        $sectorId = $request->sectorId ?? null;
        $bankId = $request->bankId ?? null;
        $startDate = $request->input('startDate', $oneYearAgo);
        $endDate = $request->input('endDate', $today);

        $query = BankLedger::query();

         // Filter by sectorId
         if ($sectorId) {
            $query->where('sectorId', operator: $sectorId);
          }

        // Filter by bankId
           if ($bankId) {
          $query->orWhere('bankId', operator: $bankId);
        }

        //filter trDate
        if ($startDate && $endDate) {
            $query->whereBetween('trDate', [$startDate, $endDate]);
        }


        // Fetch bank_ledgers with eager loading of related data
        $bank_ledgers = $query->latest()->get();

        // Check if any bank_ledgers found
        if ($bank_ledgers->isEmpty()) {
          return response()->json(['message' => 'No Bank Ledgers found', 'data' => []], 200);
        }

        // Use the BankLedgerResource to transform the data
        $transformedBankLedgers = BankLedgerResource::collection($bank_ledgers);

        // Return bank Ledgers transformed with the resource
        return response()->json([
          'message' => 'Success!',
          'data' => $transformedBankLedgers
    ],200);
    }

    public function store(BankLedgerRequest $request)
    {
        try {
            $bank_ledger = new BankLedger();
            $bank_ledger->companyId = $request->companyId;
            $bank_ledger->sectorId = $request->sectorId;
            $bank_ledger->trId = $request->trId;
            $bank_ledger->bankId = $request->bankId;
            $bank_ledger->trType = $request->trType;
            $bank_ledger->trDate = $request->trDate;
            $bank_ledger->companyBalance = $request->companyBalance;
            $bank_ledger->sectorBalance = $request->sectorBalance;
            $bank_ledger->amount = $request->amount;
            $bank_ledger->balance = $request->balance;
            $bank_ledger->particular = $request->particular;
            $bank_ledger->note = $request->note;

            $bank_ledger->save();
            //dd($bank_ledger);
            return response()->json([
              'message' => 'BankLedger created successfully',
              'data' => new BankLedgerResource($bank_ledger),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
      }

    public function show($id)
    {
        $bank_ledger = BankLedger::find($id);

        if (!$bank_ledger) {
          return response()->json(['message' => 'Bank Ledger not found'], 404);
        }
        return new BankLedgerResource($bank_ledger);
    }


    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        //
    }
}