<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SpEggStockRequest;
use App\Http\Resources\SpEggStockResource;
use App\Models\SpEggStock;
use Illuminate\Http\Request;

class SpEggStockController extends Controller
{

    public function index()
    {
        $sp_egg_stocks = SpEggStock::latest()->get();

        if($sp_egg_stocks->isEmpty()){
            return response()->json(['message' => 'No Sp Egg Stock found'], 200);
        }
        return SpEggStockResource::collection($sp_egg_stocks);
    }

    public function store(SpEggStockRequest $request)
    {
        try {
            $lastSpStock = SpEggStock::orderBy('id', 'desc')->first();
            if ($lastSpStock) {
              $nextId = $lastSpStock->id + 1;
            } else {
              $nextId = 1;
            }
            $sp_egg_stock = new SpEggStock();
            $sp_egg_stock->spId = 'spe-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
            $sp_egg_stock->stockId = $request->stockId;
            $sp_egg_stock->farmStockId = $request->farmStockId;
            $sp_egg_stock->stockDate = $request->stockDate;
            $sp_egg_stock->saleDate = $request->saleDate;

            $sp_egg_stock->save();
            // dd($sp_egg_stock);

            return response()->json([
              'message' => 'Sp Egg Stock created successfully',
              'data' => new SpEggStockResource($sp_egg_stock),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }

    }

    public function show($id)
    {
        $sp_egg_stock = SpEggStock::find($id);

        if (!$sp_egg_stock) {
          return response()->json(['message' => 'Sp Egg Stock not found'], 404);
        }
        return new SpEggStockResource($sp_egg_stock);
    }


    public function update(SpEggStockRequest $request, $id)
    {
        try {

            $sp_egg_stock = SpEggStock::find($id);

            if (!$sp_egg_stock) {
              return $this->sendError('Sp Egg Stock not found.');
            }

            $sp_egg_stock->stockId = $request->stockId;
            $sp_egg_stock->farmStockId = $request->farmStockId;
            $sp_egg_stock->stockDate = $request->stockDate;
            $sp_egg_stock->saleDate = $request->saleDate;

            $sp_egg_stock->update();


            return response()->json([
              'message' => 'Sp Egg Stock Updated successfully',
              'data' => new SpEggStockResource($sp_egg_stock),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }    }


    public function destroy($id)
    {
        $sp_egg_stock = SpEggStock::find($id);
        if (!$sp_egg_stock) {
          return response()->json(['message' => 'Sp Egg Stock not found'], 404);
        }
        $sp_egg_stock->delete();
        return response()->json([
          'message' => 'Sp Egg Stock deleted successfully',
        ], 200);
    }
}