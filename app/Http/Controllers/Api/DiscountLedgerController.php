<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountLedgerRequest;
use App\Http\Resources\DiscountLedgerResource;
use App\Models\DiscountLedger;
use Illuminate\Http\Request;

class DiscountLedgerController extends Controller
{
    public function index(Request $request)
        {

            $oneYearAgo = now()->subYear()->format('Y-m-d');
            $today = today()->format('Y-m-d');

            $saleId = $request->saleId ?? null;
            $saleCategoryId = $request->saleCategoryId ?? null;
            $dealerId = $request->dealerId ?? null;
            $totalPrice = $request->totalPrice ?? null;

            $startDate = $request->input('startDate', $oneYearAgo);
            $endDate = $request->input('endDate', $today);

            $query = DiscountLedger::query();


            // Filter by saleId
            if ($saleId) {
            $query->where('saleId', $saleId);
            }

            // Filter by saleCategoryId
            if ($saleCategoryId) {
            $query->orWhere('saleCategoryId', $saleCategoryId);
            }

            // Filter by dealerId
            if ($dealerId) {
                $query->orWhere('dealerId', $dealerId);
            }

            // Filter by totalPrice
            if ($totalPrice) {
                $query->orWhere('totalPrice', $totalPrice);
            }

            //Filter Date
            if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

            // Fetch discount ledger with eager loading of related data
            $discount_ledgers = $query->latest()->get();

            // Check if any discount ledgers found
            if ($discount_ledgers->isEmpty()) {
            return response()->json(['message' => 'No Discount Ledgers found', 'data' => []], 200);
            }

            // Use the DiscountLedgerResource to transform the data
            $transformedDiscountLedgers = DiscountLedgerResource::collection($discount_ledgers);

            // Return DiscountLedger transformed with the resource
            return response()->json([
            'message' => 'Success!',
            'data' => $transformedDiscountLedgers
            ], 200);
        }
    public function store(DiscountLedgerRequest $request)
    {
        try {

            $discount_ledger = new DiscountLedger();

            $discount_ledger->saleId = $request->saleId;
            $discount_ledger->saleCategoryId = $request->saleCategoryId;
            $discount_ledger->dealerId = $request->dealerId;
            $discount_ledger->saleDetails = json_encode($request->saleDetails);
            $discount_ledger->totalPrice = $request->totalPrice;
            $discount_ledger->discountPrice = $request->discountPrice;
            $discount_ledger->date = $request->date;

            $discount_ledger->save();
            // dd($discount_ledger);

            return response()->json([
              'message' => 'Discount Ledger created successfully',
              'data' => new DiscountLedgerResource($discount_ledger),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }

    }

    public function show( $id)
    {
        $discount_ledger = DiscountLedger::find($id);
        if (!$discount_ledger) {
          return response()->json(['message' => 'Discount Ledger not found'], 404);
        }
        return new DiscountLedgerResource($discount_ledger);
    }

    public function update(DiscountLedgerRequest $request, $id)
    {
        try {

            $discount_ledger = DiscountLedger::find($id);

            if (!$discount_ledger) {
              return $this->sendError('Discount Ledger not found.');
            }

            $discount_ledger->saleId = $request->saleId;
            $discount_ledger->saleCategoryId = $request->saleCategoryId;
            $discount_ledger->dealerId = $request->dealerId;
            $discount_ledger->saleDetails = json_encode($request->saleDetails);
            $discount_ledger->totalPrice = $request->totalPrice;
            $discount_ledger->discountPrice = $request->discountPrice;
            $discount_ledger->date = $request->date;
            $discount_ledger->appBy = auth()->id();


            $discount_ledger->update();


            return response()->json([
              'message' => 'Discount Ledger Updated successfully',
              'data' => new DiscountLedgerResource($discount_ledger),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
        }


    public function destroy($id)
    {
        $discount_ledger = DiscountLedger::find($id);
        if (!$discount_ledger) {
          return response()->json(['message' => 'Discount Ledger not found'], 404);
        }
        $discount_ledger->delete();
        return response()->json([
          'message' => 'Discount Ledger deleted successfully',
        ], 200);
    }
}