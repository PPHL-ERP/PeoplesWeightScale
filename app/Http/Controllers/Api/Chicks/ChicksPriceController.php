<?php

namespace App\Http\Controllers\Api\Chicks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chicks\ChicksPriceRequest;
use App\Http\Resources\Chicks\ChicksPriceResource;
use App\Models\ChicksPrice;
use App\Models\ChicksPriceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChicksPriceController extends Controller
{

    public function index(Request $request)
    {

        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

         // Filters
      $dealerId        = $request->dealerId;
      $productId       = $request->productId;
      $childCategoryId = $request->childCategoryId;
      $startDate      = $request->input('startDate', $oneYearAgo);
      $endDate        = $request->input('endDate', $today);
      $status         = $request->status;
      $limit          = $request->input('limit', 100); // Default 100


        $query = ChicksPrice::query();

        // Filter by dealerId
           if ($dealerId) {
          $query->Where('dealerId', $dealerId);
        }

       // Filter by productId within priceDetails
        if ($productId) {
            $query->whereHas('priceDetails', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }
       // Filter by childCategoryId within priceDetails' products
        if ($childCategoryId) {
            $query->whereHas('priceDetails.product', function ($q) use ($childCategoryId) {
                $q->where('childCategoryId', $childCategoryId);
            });
        }
         //Filter Date
         if ($startDate && $endDate) {
          $query->whereBetween('validityDate', [$startDate, $endDate]);
      }

        // Filter by status
        if ($status) {
          $query->where('status', $status);
        }

        // Fetch Chicks Price with eager loading of related data

        //$chicks_prices = $query->latest()->get();
        $chicks_prices = $query->with(['dealer','priceDetails.product.childCategory'])->latest()->paginate($limit);
        // Return paginated response
        return response()->json([
            'message' => 'Success!',
            'data' => ChicksPriceResource::collection($chicks_prices),
            'meta' => [
                'current_page' => $chicks_prices->currentPage(),
                'last_page' => $chicks_prices->lastPage(),
                'per_page' => $chicks_prices->perPage(),
                'total' => $chicks_prices->total(),
            ]
        ], 200);

    }


    public function store(ChicksPriceRequest $request)
    {
        try {


            DB::beginTransaction();

            $chicks_price = new ChicksPrice();
            $chicks_price->dealerId = $request->dealerId;
            //$chicks_price->empId = $request->empId;
            $chicks_price['empId'] = auth()->check() ? auth()->user()->id : 'N/A';
            $chicks_price->outDealerName = $request->outDealerName;
            $chicks_price->phone = $request->phone;
            $chicks_price->date = $request->date;
           // $chicks_price->validityDate = $request->validityDate;
            $chicks_price->validityDate = now();
            $chicks_price->note = $request->note;
            $chicks_price->crBy = auth()->id();
            $chicks_price->status = 'pending';

            $chicks_price->save();

            //dd($chicks_price);

            // Detail input START
            $cpId = $chicks_price->id;

            foreach ($request->input('price_details', []) as $detail) {
                $productId = $detail['productId'];
                $qty = $detail['qty'];
                $dailyPriceId = $detail['dailyPriceId'];
                $dPrice = $detail['dPrice'];
                $cPrice = $detail['cPrice'];

                $dbDetail = new ChicksPriceDetail();
                $dbDetail->cpId = $cpId;
                $dbDetail->productId = $productId;
                $dbDetail->qty = $qty;
                $dbDetail->dailyPriceId = $dailyPriceId;
                $dbDetail->dPrice = $dPrice;
                $dbDetail->cPrice = $cPrice;
                $dbDetail->save();
            }
            // Detail input END
            DB::commit();

            return response()->json([
                'message' => 'Chicks Price created successfully',
                'data' => new ChicksPriceResource($chicks_price),
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception here
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
 }


    public function show($id)
    {
        $chicks_price = ChicksPrice::find($id);
        if (!$chicks_price) {
            return response()->json(['message' => 'Chicks Price not found'], 404);
        }
        return new ChicksPriceResource($chicks_price);
    }



    public function update(ChicksPriceRequest $request,  $id)
    {
        try {
            $chicks_price = ChicksPrice::find($id);

            if (!$chicks_price) {
                return $this->sendError('Chicks Price not found.');
            }

            // Check if the ChicksPrice is approved
            if ($chicks_price->status === 'approved') {
                return response()->json(['message' => 'Cannot modify products for approved Chicks Price.'], 403);
            }

            // Update the main ChicksPrice fields

            $chicks_price->dealerId = $request->dealerId;
            $chicks_price->empId = $request->empId;
            $chicks_price->outDealerName = $request->outDealerName;
            $chicks_price->phone = $request->phone;
            $chicks_price->date = $request->date;
            $chicks_price->validityDate = $request->validityDate;
            $chicks_price->note = $request->note;
            $chicks_price->crBy = auth()->id();
            $chicks_price->status = 'pending';

            $chicks_price->save();

            // Update priceDetails
            $existingDetailIds = $chicks_price->priceDetails()->pluck('id')->toArray();

            foreach ($request->input('price_details', []) as $detail) {
                if (isset($detail['id']) && in_array($detail['id'], $existingDetailIds)) {
                    // Update existing detail
                    $sbDetail = ChicksPriceDetail::find($detail['id']);
                    $sbDetail->productId = $detail['productId'];
                    $sbDetail->qty = $detail['qty'];
                    $sbDetail->dailyPriceId = $detail['dailyPriceId'];
                    $sbDetail->dPrice = $detail['dPrice'];
                    $sbDetail->cPrice = $detail['cPrice'];
                    $sbDetail->save();

                    // Remove updated detail ID from the list of existing IDs
                    $existingDetailIds = array_diff($existingDetailIds, [$sbDetail->id]);
                } else {
                    // Create new detail if not exists
                    $sbDetail = new ChicksPriceDetail();
                    $sbDetail->cpId = $chicks_price->id;
                    $sbDetail->productId = $detail['productId'];
                    $sbDetail->qty = $detail['qty'];
                    $sbDetail->dailyPriceId = $detail['dailyPriceId'];
                    $sbDetail->dPrice = $detail['dPrice'];
                    $sbDetail->cPrice = $detail['cPrice'];
                    $sbDetail->save();
                }
            }

            // Delete removed details
            ChicksPriceDetail::whereIn('id', $existingDetailIds)->delete();

            return response()->json([
                'message' => 'Chicks Price updated successfully',
                'data' => new ChicksPriceResource($chicks_price),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
     }


     public function statusUpdate(Request $request, $id)
    {
      $chicks_price = ChicksPrice::find($id);
      $chicks_price->status = $request->status;

      $chicks_price->update();
      return response()->json([
        'message' => 'Chicks Price Status change successfully',
      ], 200);
    }

    public function destroy($id)
    {
        $chicks_price = ChicksPrice::find($id);
        if (!$chicks_price) {
            return response()->json(['message' => 'Chicks Price not found'], 404);
        }
        $chicks_price->delete();
        return response()->json([
            'message' => 'Chicks Price deleted successfully',
        ], 200);
    }
}
