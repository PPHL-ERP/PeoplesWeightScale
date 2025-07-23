<?php

namespace App\Http\Controllers\Api\Chicks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chicks\ChicksDailyPriceRequest;
use App\Http\Resources\Chicks\ChicksDailyPriceResource;
use App\Models\ChicksDailyPrice;
use App\Models\ChicksDailyPriceHistory;
use Illuminate\Http\Request;

class ChicksDailyPriceController extends Controller
{

    public function index()
    {
        $cd_prices = ChicksDailyPrice::latest()->get();

        if($cd_prices->isEmpty()){
            return response()->json(['message' => 'No Chicks Daily Price found'], 200);
        }
        return ChicksDailyPriceResource::collection($cd_prices);
    }



    public function store(ChicksDailyPriceRequest $request)
    {
        $cd_price = new ChicksDailyPrice();
        $cd_price->fill($request->all());
        $cd_price->crBy = auth()->id();
        $cd_price->status = 'pending';

        $cd_price->save();

        return response()->json([
            'message' => 'Chicks Daily Price created successfully',
            'data' => new ChicksDailyPriceResource($cd_price),
        ], 200);
    }


    public function show($id)
    {
        $cd_price = ChicksDailyPrice::find($id);
        if (!$cd_price) {
          return response()->json(['message' => 'Chicks Daily Price not found'], 404);
        }
        return new ChicksDailyPriceResource($cd_price);
    }

    public function update(ChicksDailyPriceRequest $request, $id)
    {
        try {
            $cd_price = ChicksDailyPrice::find($id);

            if (!$cd_price) {
                return response()->json([
                    'message' => 'Chicks Daily Price not found.'
                ], 404);
            }

            // Get only fillable fields to check if anything changed
            // $updatedData = $request->only($cd_price->getFillable());
            $updatedData = collect($request->only($cd_price->getFillable()))
            ->except('status') // 🚫 remove 'status' if sent
            ->toArray();
            // Check if any relevant data has changed
            $hasChanged = false;
            foreach ($updatedData as $key => $value) {
                if ($cd_price->$key != $value) {
                    $hasChanged = true;
                    break;
                }
            }

            // Update ChicksDailyPrice
            $cd_price->fill($updatedData);
            $cd_price->appBy = auth()->id();
            $cd_price->save();

            // Only proceed if status is approved
            if ($cd_price->status === 'approved') {
                if ($hasChanged) {
                    // 1️⃣ Update existing ChicksDailyPriceHistory for this chicksDPriceId to status "old"
                    ChicksDailyPriceHistory::where('chicksDPriceId', $cd_price->id)
                        ->update([
                            'status' => 'old',
                            'updated_at' => now(),
                        ]);

                    // 2️⃣ Insert new record into ChicksDailyPriceHistory with status "running"
                    ChicksDailyPriceHistory::create([
                        'chicksDPriceId' => $cd_price->id,
                        'pId' => $cd_price->pId,
                        'cZoneId' => $cd_price->cZoneId,
                        'pCost' => $cd_price->pCost,
                        'mrp' => $cd_price->mrp,
                        'salePrice' => $cd_price->salePrice,
                        'date' => now()->toDateString(),
                        'changeType' => $request->changeType,
                        'status' => 'running',
                        'crBy' => auth()->id(),
                        'appBy' => auth()->id(),
                    ]);
                }
            }

            return response()->json([
                'message' => 'Chicks Daily Price updated successfully.',
                'data' => new ChicksDailyPriceResource($cd_price),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function statusUpdate(Request $request,$id){
        $cd_price = ChicksDailyPrice::find($id);
        $cd_price->status = $request->status;
        $cd_price->appBy = auth()->id();

        $cd_price->update();


        // Create a DailyPriceHistory entry if status is 'approved'
        if ($cd_price->status === 'approved') {
            ChicksDailyPriceHistory::create(attributes: [
                'chicksDPriceId' => $cd_price->id,
                'pId' => $cd_price->pId,
                'cZoneId' => $cd_price->cZoneId,
                'pCost' => $cd_price->pCost,
                'mrp' => $cd_price->mrp,
                'salePrice' => $cd_price->salePrice,
                'date' => now()->toDateString(),
                'status' => 'running',
                'crBy' => auth()->id(),
                'appBy' => auth()->id(),
            ]);
        }

        return response()->json([
            'message' => 'Chicks Daily Price Status change successfully',
        ],200);
    }

    public function destroy($id)
    {
        $cd_price = ChicksDailyPrice::find($id);
        if (!$cd_price) {
            return response()->json(['message' => 'Chicks Daily Price not found'], 404);
        }
        $cd_price->delete();
        return response()->json([
            'message' => 'Chicks Daily Price deleted successfully',
        ],200);
    }
}
