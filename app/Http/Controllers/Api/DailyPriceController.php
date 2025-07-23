<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DailyPriceRequest;
use App\Http\Resources\DailyPriceResource;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\DailyPrice;
use App\Models\DailyPriceHistory;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class DailyPriceController extends Controller
{

    // public function index1111(Request $request)
    // {
    //     // $startDate = $request->startDate ?? now()->subMonth()->format('Y-m-d');
    //     // $endDate = $request->endDate ?? now()->format('Y-m-d');
    //     $oneYearAgo = now()->subYear()->format('Y-m-d');
    //     $today = today()->format('Y-m-d');

    //     $productId = $request->productId ?? null;
    //     $status = $request->status ?? null;
    //     $childCategoryId = $request->childCategoryId ?? null;

    //     $startDate = $request->input('startDate', $oneYearAgo);
    //     $endDate = $request->input('endDate', $today);


    //     $query = DailyPrice::query();

    //     // Filter by childCategoryId
    //     if ($childCategoryId) {
    //         $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
    //         $query->whereIn('productId', $productIds);
    //     }

    //     // Filter by productId
    //        if ($productId) {
    //       $query->where('productId', $productId);
    //     }


    //     //filter date
    //     if ($startDate && $endDate) {
    //         $query->whereBetween('date', [$startDate, $endDate]);
    //     }

    //     // Filter by status
    //     if ($status) {
    //       $query->where('status', $status);
    //     }

    //     // Fetch daily prices with eager loading of related data
    //    // $daily_prices = $query->latest()->get();
    //     $daily_prices = $query->with(['product', 'childCategory'])->latest()->get();


    //     // Check if any daily prices found
    //     if ($daily_prices->isEmpty()) {
    //       return response()->json(['message' => 'No Daily Price found', 'data' => []], 200);
    //     }

    //     // Use the DailyPriceResource to transform the data
    //     $transformedDailyPrices = DailyPriceResource::collection($daily_prices);

    //     // Return DailyPrices transformed with the resource
    //     return response()->json([
    //       'message' => 'Success!',
    //       'data' => $transformedDailyPrices
    //     ], 200);
    // }

public function indexOld(Request $request)
{
    $oneYearAgo = now()->subYear()->format('Y-m-d');
    $today = today()->format('Y-m-d');

    $categoryId = $request->categoryId ?? null;
    $subCategoryId = $request->subCategoryId ?? null;
    $childCategoryId = $request->childCategoryId ?? null;
    $productId = $request->productId ?? null;
    $status = $request->status ?? null;

    $startDate = $request->input('startDate', $oneYearAgo);
    $endDate = $request->input('endDate', $today);

    $query = DailyPrice::query();

    // Filter by categoryId
    if ($categoryId) {
        $query->whereHas('product', function ($query) use ($categoryId) {
            $query->where('categoryId', $categoryId);
        });
    }

    // Filter by subCategoryId
    if ($subCategoryId) {
        $query->whereHas('product', function ($query) use ($subCategoryId) {
            $query->where('subCategoryId', $subCategoryId);
        });
    }

    // Filter by childCategoryId
    if ($childCategoryId) {
        $query->whereHas('product', function ($query) use ($childCategoryId) {
            $query->where('childCategoryId', $childCategoryId);
        });
    }

    // Filter by productId
    if ($productId) {
        $query->where('productId', $productId);
    }

    // Filter by date range
    if ($startDate && $endDate) {
        $query->whereBetween('date', [$startDate, $endDate]);
    }

    // Filter by status
    if ($status) {
        $query->where('status', $status);
    }

    // Fetch daily prices with eager loading of related data
    $daily_prices = $query->with(['product.category', 'product.subCategory', 'product.childCategory'])->latest()->get();

    // Check if any daily prices found
    if ($daily_prices->isEmpty()) {
        return response()->json(['message' => 'No Daily Price found', 'data' => []], 200);
    }

    // Use the DailyPriceResource to transform the data
    $transformedDailyPrices = DailyPriceResource::collection($daily_prices);

    // Return DailyPrices transformed with the resource
    return response()->json([
        'message' => 'Success!',
        'data' => $transformedDailyPrices
    ], 200);
}

public function index(Request $request)
{
    $oneYearAgo = now()->subYear()->format('Y-m-d');
    $today = today()->format('Y-m-d');

    // Filters
    $categoryId       = $request->categoryId;
    $subCategoryId        = $request->subCategoryId;
    $childCategoryId  = $request->childCategoryId;
    $productId   = $request->productId;
    $status         = $request->status;
    $startDate      = $request->input('startDate', $oneYearAgo);
    $endDate        = $request->input('endDate', $today);
    $limit          = $request->input('limit', 100); // Default 100

    $query = DailyPrice::query();

    // Filter by categoryId
    if ($categoryId) {
        $query->whereHas('product', function ($query) use ($categoryId) {
            $query->where('categoryId', $categoryId);
        });
    }

    // Filter by subCategoryId
    if ($subCategoryId) {
        $query->whereHas('product', function ($query) use ($subCategoryId) {
            $query->where('subCategoryId', $subCategoryId);
        });
    }

    // Filter by childCategoryId
    if ($childCategoryId) {
        $query->whereHas('product', function ($query) use ($childCategoryId) {
            $query->where('childCategoryId', $childCategoryId);
        });
    }

    // Filter by productId
    if ($productId) {
        $query->where('productId', $productId);
    }

    // Filter by date range
    if ($startDate && $endDate) {
        $query->whereBetween('date', [$startDate, $endDate]);
    }

    // Filter by status
    if ($status) {
        $query->where('status', $status);
    }

    // Fetch daily prices with eager loading of related data
    $daily_prices = $query->with(['product.category', 'product.subCategory', 'product.childCategory'])->latest()->paginate($limit);

     // Return paginated response
     return response()->json([
        'message' => 'Success!',
        'data' => DailyPriceResource::collection($daily_prices),
        'meta' => [
            'current_page' => $daily_prices->currentPage(),
            'last_page' => $daily_prices->lastPage(),
            'per_page' => $daily_prices->perPage(),
            'total' => $daily_prices->total(),
        ]
    ], 200);
}

    public function store(DailyPriceRequest $request)
    {
        $daily_price = new DailyPrice();
        $daily_price->fill($request->all());
        $daily_price->crBy = auth()->id();
        $daily_price->status = 'pending';

        $daily_price->save();

        return response()->json([
            'message' => 'Daily Price created successfully',
            'data' => new DailyPriceResource($daily_price),
        ], 200);
    }


    public function show( $id)
    {
        $daily_price = DailyPrice::find($id);
        if (!$daily_price) {
          return response()->json(['message' => 'Daily Price not found'], 404);
        }
        return new DailyPriceResource($daily_price);
    }


//     public function update(DailyPriceRequest $request, $id)
//     {
//         try {
//             $daily_price = DailyPrice::find($id);
//             if (!$daily_price) {
//                 return $this->sendError('Daily Price not found.');
//             }

//             // Store the current currentPrice before updating
//             $currentPrice = $daily_price->currentPrice;
//             $crBy = $daily_price->crBy;
//             $appBy = $daily_price->appBy;


//             $daily_price->fill($request->all());

//          // Check if currentPrice has changed
//          if ($request->has('currentPrice') && $daily_price->currentPrice != $currentPrice) {
//             // Update the oldPrice with the previous currentPrice
//             $daily_price->oldPrice = $currentPrice;

//             // Log the price change in DailyPriceHistory
//             DailyPriceHistory::create([
//                     'dailyPriceId' => $daily_price->id,
//                     'productId' => $daily_price->productId,
//                     'categoryId' => $daily_price->categoryId,
//                     'availableQty' => $daily_price->availableQty,
//                     'newPrice' => $request->currentPrice, // New price
//                     'price' => $currentPrice, // Old price
//                     'date' => now()->toDateString(),
//                     'time' => now()->toTimeString(),
//                     'crBy' => auth()->id(),
//             ]);
//         }

//             $daily_price->crBy = $crBy;
//             $daily_price->appBy = $appBy;
//             $daily_price->status = 'pending';


//             $daily_price->update();

//             return response()->json([
//                 'message' => 'Daily Price List Updated Successfully',
//                 'data' => new DailyPriceResource($daily_price),
//             ], 200);
//         } catch (\Exception $e) {
//             return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
//     }
//  }




// public function update(DailyPriceRequest $request, $id)
// {
//     try {
//         $daily_price = DailyPrice::find($id);

//         if (!$daily_price) {
//             return $this->sendError('Daily Price not found.');
//         }

//         // Store the current values before updating
//         $currentPrice = $daily_price->currentPrice;
//         $crBy = $daily_price->crBy;
//         $appBy = $daily_price->appBy;

//         // Fill the new data
//         $daily_price->fill($request->all());

//         $priceChanged = $request->has('currentPrice') && $daily_price->currentPrice != $currentPrice;

//         // If currentPrice is updated, log it in DailyPriceHistory
//         if ($priceChanged) {
//             $daily_price->oldPrice = $currentPrice; // Update old price

//             // Log the change in the DailyPriceHistory
//             DailyPriceHistory::create([
//                 'dailyPriceId' => $daily_price->id,
//                 'productId' => $daily_price->productId,
//                 'categoryId' => $daily_price->categoryId,
//                 'availableQty' => $daily_price->availableQty,
//                 'newPrice' => $request->currentPrice, // New price
//                 'price' => $currentPrice, // Old price
//                 'date' => now()->toDateString(),
//                 'time' => now()->toTimeString(),
//                 'crBy' => auth()->id(),
//             ]);
//         }

//         // Ensure crBy and appBy values remain unchanged
//         $daily_price->crBy = $crBy;
//         $daily_price->appBy = $appBy;

//         // If currentPrice changed, set status to 'pending' for approval
//         $daily_price->status = $priceChanged ? 'pending' : $daily_price->status;

//         // Save the updated data
//         $daily_price->update();

//         return response()->json([
//             'message' => 'Daily Price List Updated Successfully',
//             'data' => new DailyPriceResource($daily_price),
//         ], 200);
//     } catch (\Exception $e) {
//         return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
//     }
// }


public function update(DailyPriceRequest $request, $id)
{
    try {
        $daily_price = DailyPrice::find($id);

        if (!$daily_price) {
            return $this->sendError('Daily Price not found.');
        }

        // Store the current values before updating
        $currentPrice = $daily_price->currentPrice;
        $crBy = $daily_price->crBy;
        $appBy = $daily_price->appBy;

        // Fill the new data
        $daily_price->fill($request->all());

        // Track if the price has changed
        $priceChanged = $request->has('currentPrice') && $daily_price->currentPrice != $currentPrice;

        // If currentPrice is updated, update old price
        if ($priceChanged) {
            $daily_price->oldPrice = $currentPrice;
        }

        // Ensure crBy and appBy values remain unchanged
        $daily_price->crBy = $crBy;
        $daily_price->appBy = $appBy;

        // Set status to 'pending' if the price changed
        $daily_price->status = $priceChanged ? 'pending' : $daily_price->status;

        // Save the updated data
        $daily_price->update();

        // If the status is 'approved' and the price was changed, log the history
        if ($priceChanged && $daily_price->status === 'approved') {
            DailyPriceHistory::create([
                'dailyPriceId' => $daily_price->id,
                'productId' => $daily_price->productId,
                'categoryId' => $daily_price->categoryId,
                'availableQty' => $daily_price->availableQty,
                'newPrice' => $daily_price->currentPrice, // New price after approval
                'price' => $currentPrice, // Old price
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
                'crBy' => auth()->id(),
            ]);
        }

        return response()->json([
            'message' => 'Daily Price List Updated Successfully',
            'data' => new DailyPriceResource($daily_price),
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}

public function statusUpdate(Request $request,$id){
        $daily_price = DailyPrice::find($id);
        $daily_price->status = $request->status;
        $daily_price->appBy = auth()->id();

        $daily_price->update();


        // Create a DailyPriceHistory entry if status is 'approved'
        if ($daily_price->status === 'approved') {
            DailyPriceHistory::create([
                'dailyPriceId' => $daily_price->id,
                'productId' => $daily_price->productId,
                'categoryId' => $daily_price->categoryId,
                'availableQty' => $daily_price->availableQty,
                'price' => $daily_price->oldPrice, // Old price
                'newPrice' => $daily_price->currentPrice, // New price
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
                'crBy' => auth()->id(),
            ]);
        }


        return response()->json([
            'message' => 'Daily Price Status change successfully',
        ],200);
    }

    public function destroy($id)
    {
        $daily_price = DailyPrice::find($id);
        if (!$daily_price) {
            return response()->json(['message' => 'Daily Price not found'], 404);
        }
        $daily_price->delete();
        return response()->json([
            'message' => 'Daily Price deleted successfully',
        ],200);
    }
}