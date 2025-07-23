<?php

namespace App\Http\Controllers\Api\Chicks;

use App\Http\Controllers\Controller;
use App\Http\Resources\Chicks\ChicksProductionLedgerResource;
use App\Models\ChicksProductionLedger;
use App\Models\Product;
use Illuminate\Http\Request;

class ChicksProductionLedgerController extends Controller
{

    public function index(Request $request)
    {

         $oneYearAgo = now()->subYear()->format('Y-m-d');
         $today = today()->format('Y-m-d');

        // Filters
        $hatcheryId        = $request->hatcheryId;
        $productId       = $request->productId;
        $childCategoryId = $request->childCategoryId;
        $transactionType = $request->transactionType;
        $startDate      = $request->input('startDate', $oneYearAgo);
        $endDate        = $request->input('endDate', $today);
        $limit          = $request->input('limit', 100); // Default 100


         $query = ChicksProductionLedger::query();

        // Filter by hatcheryId
        if ($hatcheryId) {
            $query->where('hatcheryId', $hatcheryId);
        }

         // Filter by childCategoryId
         if ($childCategoryId) {
            $productIds = Product::where('childCategoryId', $childCategoryId)->pluck('id');
            $query->whereIn('productId', $productIds);
        }

        // Filter by productId
        if ($productId) {
            $query->where('productId', $productId);
        }

        // Filter Date
        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        // Filter by single transactionType
        if ($transactionType) {
            $query->where('trType', $transactionType);
        }

       // $cpLedger = $query->latest()->get();
        $cpLedger = $query->with(['product', 'childCategory'])->latest()->paginate($limit);


     // Return paginated response
     return response()->json([
        'message' => 'Success!',
        'data' => ChicksProductionLedgerResource::collection($cpLedger),
        'meta' => [
            'current_page' => $cpLedger->currentPage(),
            'last_page' => $cpLedger->lastPage(),
            'per_page' => $cpLedger->perPage(),
            'total' => $cpLedger->total(),
        ]
    ], 200);

    }


    public function store(Request $request)
    {
        //
    }


    public function show(string $id)
    {
        //
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
