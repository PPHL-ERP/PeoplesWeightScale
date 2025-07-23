<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FarmEggStockRequest;
use App\Http\Resources\FarmEggStockResource;
use App\Models\FarmEggStock;
use Illuminate\Http\Request;

class FarmEggStockController extends Controller
{

    public function index()
    {
        $farm_egg_stocks = FarmEggStock::latest()->get();

        if($farm_egg_stocks->isEmpty()){
            return response()->json(['message' => 'No Farm Egg Stock found'], 200);
        }
        return FarmEggStockResource::collection($farm_egg_stocks);
   }


    public function store(FarmEggStockRequest $request)
    {
        try {

            $farm_egg_stock = new FarmEggStock();

            $farm_egg_stock->sectorId = $request->sectorId;
            $farm_egg_stock->stockDate = $request->stockDate;
            $farm_egg_stock->dEgg = $request->dEgg;
            $farm_egg_stock->bEgg = $request->bEgg;
            $farm_egg_stock->mEgg = $request->mEgg;
            $farm_egg_stock->smEgg = $request->smEgg;
            $farm_egg_stock->brokenEgg = $request->brokenEgg;
            $farm_egg_stock->liqEgg = $request->liqEgg;
            $farm_egg_stock->wasteEgg = $request->wasteEgg;
            $farm_egg_stock->adjEgg = $request->adjEgg;
            $farm_egg_stock->others = $request->others;
            $farm_egg_stock->note = $request->note;
            $farm_egg_stock->crBy = auth()->id();
            $farm_egg_stock->status = 'pending';

            $farm_egg_stock->save();
            // dd($farm_egg_stock);

            return response()->json([
              'message' => 'Farm Egg Stock created successfully',
              'data' => new FarmEggStockResource($farm_egg_stock),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
       }


    public function show($id)
    {
        $farm_egg_stock = FarmEggStock::find($id);

        if (!$farm_egg_stock) {
          return response()->json(['message' => 'Farm Egg Stock not found'], 404);
        }
        return new FarmEggStockResource($farm_egg_stock);
    }


    public function update(FarmEggStockRequest $request,$id)
    {
        try {

            $farm_egg_stock = FarmEggStock::find($id);

            if (!$farm_egg_stock) {
              return $this->sendError('Farm Egg Stock not found.');
            }

            $farm_egg_stock->sectorId = $request->sectorId;
            $farm_egg_stock->stockDate = $request->stockDate;
            $farm_egg_stock->dEgg = $request->dEgg;
            $farm_egg_stock->bEgg = $request->bEgg;
            $farm_egg_stock->mEgg = $request->mEgg;
            $farm_egg_stock->smEgg = $request->smEgg;
            $farm_egg_stock->brokenEgg = $request->brokenEgg;
            $farm_egg_stock->liqEgg = $request->liqEgg;
            $farm_egg_stock->wasteEgg = $request->wasteEgg;
            $farm_egg_stock->adjEgg = $request->adjEgg;
            $farm_egg_stock->others = $request->others;
            $farm_egg_stock->note = $request->note;
            $farm_egg_stock->status = $request->status;

            $farm_egg_stock->update();


            return response()->json([
              'message' => 'Farm Egg Stock Updated successfully',
              'data' => new FarmEggStockResource($farm_egg_stock),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
    }

      public function statusUpdate(Request $request, $id)
      {
        $farm_egg_stock = FarmEggStock::find($id);
        $farm_egg_stock->status = $request->status;
        $farm_egg_stock->appBy = auth()->id();

        $farm_egg_stock->update();
        return response()->json([
          'message' => 'Farm Egg Stock Status change successfully',
        ], 200);
      }

    public function destroy($id)
    {
        $farm_egg_stock = FarmEggStock::find($id);
        if (!$farm_egg_stock) {
          return response()->json(['message' => 'Farm Egg Stock not found'], 404);
        }
        $farm_egg_stock->delete();
        return response()->json([
          'message' => 'Farm Egg Stock deleted successfully',
        ], 200);
    }
}