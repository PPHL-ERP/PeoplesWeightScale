<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyLedgerRequest;
use App\Http\Resources\CompanyLedgerResource;
use App\Models\CompanyLedger;
use Illuminate\Http\Request;

class CompanyLedgerController extends Controller
{

    public function index(Request $request)
    {
        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

        $trId = $request->trId ?? null;
        $companyId = $request->companyId ?? null;
        $startDate = $request->input('startDate', $oneYearAgo);
        $endDate = $request->input('endDate', $today);

        $query = CompanyLedger::query();

         // Filter by trId
         if ($trId) {
            $query->where('trId', operator: $trId);
          }

        // Filter by companyId
           if ($companyId) {
          $query->orWhere('companyId', $companyId);
        }

        //filter date
        if ($startDate && $endDate) {
            $query->whereBetween('transactionDate', [$startDate, $endDate]);
        }


        // Fetch com_ledgers with eager loading of related data
        $com_ledgers = $query->latest()->get();

        // Check if any com_ledgers found
        if ($com_ledgers->isEmpty()) {
          return response()->json(['message' => 'No Company Ledgers found', 'data' => []], 200);
        }

        // Use the CompanyLedgerResource to transform the data
        $transformedCompanyLedgers = CompanyLedgerResource::collection($com_ledgers);

        // Return company Ledgers transformed with the resource
        return response()->json([
          'message' => 'Success!',
          'data' => $transformedCompanyLedgers
    ],200);
    }
    public function store(CompanyLedgerRequest $request)
    {
        try {
            $com_ledger = new CompanyLedger();
            $com_ledger->trId = $request->trId;
            $com_ledger->typeId = $request->typeId;
            $com_ledger->companyId = $request->companyId;
            $com_ledger->accountHead = $request->accountHead;
            $com_ledger->transactionDate = $request->transactionDate;
            $com_ledger->trType = $request->trType;
            $com_ledger->amount = $request->amount;
            $com_ledger->balance = $request->balance;
            $com_ledger->particular = $request->particular;
            $com_ledger->save();
            //dd($com_ledger);
            return response()->json([
              'message' => 'Company Ledger created successfully',
              'data' => new CompanyLedgerResource($com_ledger),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
      }


    public function show($id)
    {
        $com_ledger = CompanyLedger::find($id);

        if (!$com_ledger) {
          return response()->json(['message' => 'Company Ledger not found'], 404);
        }
        return new CompanyLedgerResource($com_ledger);
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