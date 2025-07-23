<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommissionFormRequest;
use App\Http\Requests\CommissionUpdateFormRequest;
use App\Http\Resources\Feed\FeedCommissionResource;
use App\Models\Commission;
use App\Models\CommissionProduct;
use App\Models\DealersAssignedCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CommissionController extends Controller
{
    // public function index()
    // {
    //     $companyId = $request->companyId ?? null;
    //     $commissionNo = $request->commissionNo ?? null;
    //     $fromDate = $request->fromDate ?? null;
    //     $toDate = $request->toDate ?? null;
    //     $commissionType = $request->commissionType ?? null;
    //     $categoryId = $request->categoryId ?? null;
    //     $dealerId = $request->dealerId ?? null;
    //     $zoneId = $request->zoneId ?? null;
    //     $status = $request->status ?? null;
    //     $query = Commission::query();

    //     if ($companyId) {
    //         $query->where('companyId', $companyId);
    //     }

    //     if ($commissionNo) {
    //         $query->where('commissionNo', 'LIKE', '%' . $commissionNo . '%');
    //     }

    //     if ($fromDate && $toDate) {
    //         $query->whereBetween('commissionDate', [$fromDate, $toDate]);
    //     }

    //     if ($commissionType) {
    //         $query->where('commissionType', $commissionType);
    //     }

    //     if ($categoryId) {
    //         $query->where('categoryId', $categoryId);
    //     }

    //     if ($dealerId) {
    //         $query->where('dealerId', $dealerId);
    //     }

    //     if ($zoneId) {
    //         $query->where('zoneId', $zoneId);
    //     }

    //     if ($status) {
    //         $query->where('status', $status);
    //     }

    //     $commissions = $query->with('zone', 'dealer')->latest()->get();

    //     if ($commissions->isEmpty()) {
    //         return response()->json(['message' => 'No Commission found', 'data' => []], 200);
    //     }

    //     return response()->json(['message' => 'Commission found', 'data' => $commissions], 200);
    // }

    public function indexOld(Request $request)
    {

        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

        $commissionNo = $request->commissionNo ?? null;
        $companyId = $request->companyId ?? null;
        $commissionType = $request->commissionType ?? null;
        $dealerId = $request->dealerId ?? null;
        $zoneId = $request->zoneId ?? null;
        //$categoryId = $request->categoryId ?? null;
        $productId = $request->productId ?? null;
        $childCategoryId = $request->childCategoryId ?? null;

        $startDate = $request->input('startDate', $oneYearAgo);
        $endDate = $request->input('endDate', $today);

        $status = $request->status ?? null;

        $query = Commission::query();

        // Filter by commissionNo
        if ($commissionNo) {
            $query->where('commissionNo', 'LIKE', '%' . $commissionNo . '%');
        }
        // Filter by companyId
        if ($companyId) {
            $query->orWhere('companyId', $companyId);
        }
          // Filter by commissionType
          if ($commissionType) {
            $query->orWhere('commissionType', $commissionType);
        }
        // Filter by dealerId
        if ($dealerId) {
            $query->orWhere('dealerId', $dealerId);
        }
        // Filter by zoneId
        if ($zoneId) {
            $query->orWhere('zoneId', $zoneId);
        }
        // Filter by categoryId
        // if ($categoryId) {
        //     $query->orWhere('categoryId', operator: $categoryId);
        // }

        // Filter by productId within salesOrderDetails
        if ($productId) {
            $query->whereHas('commissionProducts', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }
        // Filter by childCategoryId within commissionProducts' products
        if ($childCategoryId) {
            $query->whereHas('commissionProducts.product', function ($q) use ($childCategoryId) {
                $q->where('childCategoryId', $childCategoryId);
            });
}
        //Filter Date
        if ($startDate && $endDate) {
            $query->whereBetween('commissionDate', [$startDate, $endDate]);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Fetch commissions with eager loading of related data
        //$commissions = $query->latest()->get();
        $commissions = $query->with(['commissionProducts.product.childCategory'])->latest()->get();

        // Check if any commissions found
        if ($commissions->isEmpty()) {
            return response()->json(['message' => 'No Commissions found', 'data' => []], 200);
        }

        // Use the FeedCommissionResource to transform the data
        $transformedCommissions = FeedCommissionResource::collection($commissions);

        // Return commissions transformed with the resource
        return response()->json([
            'message' => 'Success!',
            'data' => $transformedCommissions
        ], 200);
    }

    public function index(Request $request)
    {

        $oneYearAgo = now()->subYear()->format('Y-m-d');
        $today = today()->format('Y-m-d');

       // Filters
        $commissionNo    = $request->commissionNo;
        $companyId    = $request->companyId;
        $commissionType    = $request->commissionType;
        $dealerId       = $request->dealerId;
        $zoneId      = $request->zoneId;
        //$categoryId      = $request->categoryId;
        $productId = $request->productId;
        $childCategoryId = $request->childCategoryId;
        $startDate      = $request->input('startDate', $oneYearAgo);
        $endDate        = $request->input('endDate', $today);
        $status         = $request->status;
        $limit          = $request->input('limit', 100); // Default 100

        $query = Commission::query();

        // Filter by commissionNo
        if ($commissionNo) {
            $query->where('commissionNo', 'LIKE', '%' . $commissionNo . '%');
        }
        // Filter by companyId
        if ($companyId) {
            $query->where('companyId', $companyId);
        }
          // Filter by commissionType
          if ($commissionType) {
            $query->where('commissionType', $commissionType);
        }
        // Filter by dealerId
        if ($dealerId) {
            $query->where('dealerId', $dealerId);
        }
        // Filter by zoneId
        if ($zoneId) {
            $query->where('zoneId', $zoneId);
        }
        // Filter by categoryId
        // if ($categoryId) {
        //     $query->orWhere('categoryId', operator: $categoryId);
        // }

        // Filter by productId within salesOrderDetails
        if ($productId) {
            $query->whereHas('commissionProducts', function ($q) use ($productId) {
                $q->where('productId', $productId);
            });
        }
        // Filter by childCategoryId within commissionProducts' products
        if ($childCategoryId) {
            $query->whereHas('commissionProducts.product', function ($q) use ($childCategoryId) {
                $q->where('childCategoryId', $childCategoryId);
            });
}
        //Filter Date
        if ($startDate && $endDate) {
            $query->whereBetween('commissionDate', [$startDate, $endDate]);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Fetch commissions with eager loading of related data
        //$commissions = $query->latest()->get();
        $commissions = $query->with(['commissionProducts.product.childCategory'])->latest()->paginate($limit);

        return response()->json([
            'message' => 'Success!',
            'data' => FeedCommissionResource::collection($commissions),
            'meta' => [
                'current_page' => $commissions->currentPage(),
                'last_page' => $commissions->lastPage(),
                'per_page' => $commissions->perPage(),
                'total' => $commissions->total(),
            ]
        ], 200);
    }

    public function store(CommissionFormRequest $request)
    {
        DB::beginTransaction();
        try {
            $dataArr = [];
            foreach ($request->fields as $field) {
                $commission = new Commission();
                $commission->companyId = $field['companyId'];
                $commission->commissionDate = $field['commissionDate'];
                $commission->commissionType = $field['commissionType'];
                $commission->categoryId = $field['categoryId'] ?? null;
                $commission->dealerId = $field['dealerId'] ?? null;
                $commission->zoneId = $field['zoneId'] ?? null;
                $commission->note = $field['note'];
                $commission->status = 'pending';
                $commission->crBy = auth()->user()->id;
                $commission->save();
                foreach ($field['products'] as $key => $productDetails) {
                    $commissionProduct = new CommissionProduct();
                    $commissionProduct->commissionId = $commission->id;
                    $commissionProduct->productId = $productDetails['productId'];
                    $commissionProduct->generalCommissionPercentagePerBag = $productDetails['generalCommissionPercentagePerBag'];
                    $commissionProduct->cashIncentivePerBag = $productDetails['cashIncentivePerBag'];
                    $commissionProduct->monthlyTargetQuantity = $productDetails['monthlyTargetQuantity'];
                    $commissionProduct->monthlyTargetPerBagCashAmount = $productDetails['monthlyTargetPerBagCashAmount'];
                    $commissionProduct->yearlyTargetQuantity = $productDetails['yearlyTargetQuantity'];
                    $commissionProduct->yearlyTargetPerBagCashAmount = $productDetails['yearlyTargetPerBagCashAmount'];
                    $commissionProduct->perBagTransportDiscountAmount = $productDetails['perBagTransportDiscountAmount'];
                    $commissionProduct->specialTargetQuantity = $productDetails['specialTargetQuantity'];
                    $commissionProduct->specialTargetPerBagCashAmount = $productDetails['specialTargetPerBagCashAmount'];
                    $commissionProduct->incentiveCashBack = $productDetails['incentiveCashBack'];
                    $commissionProduct->currentProductAmount = $productDetails['currentProductAmount'];
                    $commissionProduct->productStatus = 'active';
                    $commissionProduct->save();
                }
                $dataArr[] = $commission;
            }
            DB::commit();
            return response()->json(['message' => 'Commission created successfully', 'data' => $dataArr], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    // public function show($id)
    // {
    //     $commission = Commission::find($id);
    //     if (!$commission) {
    //         return response()->json(['message' => 'Commission not found'], 404);
    //     }
    //     return response()->json(['message' => 'Commission found', 'data' => $commission], 200);
    // }

    public function show(string $id)
    {
        $commission = Commission::find($id);
        if (!$commission) {
          return response()->json(['message' => 'Feed commission not found'], 404);
        }
        return new FeedCommissionResource($commission);
    }

    public function update(CommissionUpdateFormRequest $request, $id)
    {
        $commission = Commission::find($id);
        if (!$commission) {
            return response()->json(['message' => 'Commission not found'], 404);
        }
        if ($commission->status == 'approved') {
            return response()->json(['message' => 'Commission already approved'], 400);
        }
        DB::beginTransaction();
        try {
            $commission->companyId = $request->companyId;
            $commission->commissionDate = $request->commissionDate;
            $commission->commissionType = $request->commissionType;
            $commission->categoryId = $request->categoryId ?? null;
            $commission->dealerId = $request->dealerId ?? null;
            $commission->zoneId = $request->zoneId ?? null;
            $commission->note = $request->note;
            $commission->status = 'pending';
            $commission->save();
            CommissionProduct::where('commissionId', $commission->id)->delete();
            foreach ($request->products as $key => $productDetails) {
                $commissionProduct = new CommissionProduct();
                $commissionProduct->commissionId = $commission->id;
                $commissionProduct->productId = $productDetails['productId'];
                $commissionProduct->generalCommissionPercentagePerBag = $productDetails['generalCommissionPercentagePerBag'];
                $commissionProduct->cashIncentivePerBag = $productDetails['cashIncentivePerBag'];
                $commissionProduct->monthlyTargetQuantity = $productDetails['monthlyTargetQuantity'];
                $commissionProduct->monthlyTargetPerBagCashAmount = $productDetails['monthlyTargetPerBagCashAmount'];
                $commissionProduct->yearlyTargetQuantity = $productDetails['yearlyTargetQuantity'];
                $commissionProduct->yearlyTargetPerBagCashAmount = $productDetails['yearlyTargetPerBagCashAmount'];
                $commissionProduct->perBagTransportDiscountAmount = $productDetails['perBagTransportDiscountAmount'];
                $commissionProduct->specialTargetQuantity = $productDetails['specialTargetQuantity'];
                $commissionProduct->specialTargetPerBagCashAmount = $productDetails['specialTargetPerBagCashAmount'];
                $commissionProduct->incentiveCashBack = $productDetails['incentiveCashBack'];
                $commissionProduct->save();
            }
            DB::commit();
            return response()->json(['message' => 'Commission updated successfully', 'data' => $commission], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $commission = Commission::find($id);
        if (!$commission) {
            return response()->json(['message' => 'Commission not found'], 404);
        }
        if ($commission->status == 'approved') {
            return response()->json(['message' => 'Commission already approved'], 400);
        }
        $commission->delBy = auth()->user()->id;
        $commission->save();
        $commission->delete();
        return response()->json(['message' => 'Commission deleted successfully'], 200);
    }

    public function changeStatus(Request $request, $id)
    {
        $commission = Commission::find($id);
        if (!$commission) {
            return response()->json(['message' => 'Commission not found'], 404);
        }

        // Prevent updates if the record is already marked as "old"
        if ($commission->status === 'old') {
            return response()->json(['message' => 'Cannot change status of an old record'], 400);
        }

        DB::beginTransaction();
        try {
            // Update current commission's status
            $commission->status = $request->status;
            $commission->appBy = auth()->id();
            $commission->save();

            // If the status is updated to approved, mark other approved commissions for the same dealer as "old"
            if ($request->status === 'approved' && $commission->dealerId != null) {
                Commission::where('dealerId', $commission->dealerId)
                    ->where('id', '<>', $commission->id)
                    ->where('status', 'approved') // only update records that are still approved
                    ->update([
                        'status' => 'old',
                        'appBy' => auth()->id()
                    ]);

                // Update or create the dealer assigned commission as before
                DealersAssignedCommission::updateOrCreate(
                    ['dealerId' => $commission->dealerId],
                    [
                        'commissionId' => $commission->id,
                        'status' => 'approved'
                    ]
                );
            }
            DB::commit();
            return response()->json(['message' => 'Commission status updated successfully', 'data' => $commission], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function getCommissionList()
{

    $commList = Commission::with(['dealer'])
        ->where('status', 'approved')
        ->select('id', 'commissionNo', 'dealerId')
        ->get();


    $commList = $commList->map(function($comms) {
        return [
            'id' => $comms->id,
            'commissionNo' => $comms->commissionNo,
            'dealer' => [
                'id' => $comms->dealer->id ?? null,
                'tradeName' => $comms->dealer->tradeName ?? null,
                'dealerCode' => $comms->dealer->dealerCode ?? null,
                //'zoneName' => $comms->dealer->zone->zoneName ?? null,
            ],
        ];
    });

    return response()->json([
        'data' => $commList
    ], 200);
}


//with productName
// public function getDealerCommissions($dealerId)
// {
//     try {
//         $cacheKey = 'dealer_commissions_' . $dealerId;

//         $commissions = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($dealerId) {
//             return Commission::where('dealerId', $dealerId)
//                 ->where('status', 'approved')
//                 ->with(['commissionProducts.product']) // Eager Load Product
//                 ->orderByDesc('commissionDate')
//                 ->get();
//         });

//         $latestProducts = [];
//         foreach ($commissions as $commission) {
//             foreach ($commission->commissionProducts as $commissionProduct) {
//                 $productId = $commissionProduct->productId;

//                 if (!isset($latestProducts[$productId])) {
//                     $latestProducts[$productId] = [
//                         'productId' => $productId,
//                         'productName' => $commissionProduct->product->productName ?? 'N/A', // Eager Loaded Data
//                         'generalCommissionPercentagePerBag' => $commissionProduct->generalCommissionPercentagePerBag,
//                         'cashIncentivePerBag' => $commissionProduct->cashIncentivePerBag,
//                         'monthlyTargetQuantity' => $commissionProduct->monthlyTargetQuantity,
//                         'monthlyTargetPerBagCashAmount' => $commissionProduct->monthlyTargetPerBagCashAmount,
//                         'yearlyTargetQuantity' => $commissionProduct->yearlyTargetQuantity,
//                         'yearlyTargetPerBagCashAmount' => $commissionProduct->yearlyTargetPerBagCashAmount,
//                         'perBagTransportDiscountAmount' => $commissionProduct->perBagTransportDiscountAmount,
//                         'specialTargetQuantity' => $commissionProduct->specialTargetQuantity,
//                         'specialTargetPerBagCashAmount' => $commissionProduct->specialTargetPerBagCashAmount,
//                         'incentiveCashBack' => $commissionProduct->incentiveCashBack,
//                     ];
//                 }
//             }
//         }

//         return response()->json(['data' => array_values($latestProducts)], 200);
//     } catch (\Exception $e) {
//         return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
//     }
// }

//with serialize
public function getDealerCommissions($dealerId)
{
    try {
        $cacheKey = 'dealer_commissions_' . $dealerId;

        $commissions = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($dealerId) {
            return Commission::where('dealerId', $dealerId)
                ->where('status', 'approved')
                ->with(['commissionProducts.product'])
                ->get();
        });

        $latestProducts = [];
        foreach ($commissions as $commission) {
            foreach ($commission->commissionProducts as $commissionProduct) {
                $productId = $commissionProduct->productId;
                $basePrice = $commissionProduct->product->basePrice ?? 0;
                $commissionPercentage = $commissionProduct->generalCommissionPercentagePerBag;
                $commissionAmount = ($basePrice * $commissionPercentage) / 100;
                $totalprice=($basePrice+$commissionProduct->incentiveCashBack)-($commissionAmount+$commissionProduct->cashIncentivePerBag+$commissionProduct->monthlyTargetPerBagCashAmount+$commissionProduct->yearlyTargetPerBagCashAmount+$commissionProduct->specialTargetPerBagCashAmount+$commissionProduct->perBagTransportDiscountAmount);
                if (!isset($latestProducts[$productId])) {
                    $latestProducts[$productId] = [
                        'productId' => $productId,
                        //'productName' => $commissionProduct->product->productName ?? 'N/A',
                        'productName' => $commissionProduct->product->shortName ?? 'N/A',
                        'basePrice' => $totalprice,
                        'generalCommissionPercentagePerBag' => $commissionProduct->generalCommissionPercentagePerBag,
                        'cashIncentivePerBag' => $commissionProduct->cashIncentivePerBag,
                        'monthlyTargetQuantity' => $commissionProduct->monthlyTargetQuantity,
                        'monthlyTargetPerBagCashAmount' => $commissionProduct->monthlyTargetPerBagCashAmount,
                        'yearlyTargetQuantity' => $commissionProduct->yearlyTargetQuantity,
                        'yearlyTargetPerBagCashAmount' => $commissionProduct->yearlyTargetPerBagCashAmount,
                        'perBagTransportDiscountAmount' => $commissionProduct->perBagTransportDiscountAmount,
                        'specialTargetQuantity' => $commissionProduct->specialTargetQuantity,
                        'specialTargetPerBagCashAmount' => $commissionProduct->specialTargetPerBagCashAmount,
                        'incentiveCashBack' => $commissionProduct->incentiveCashBack,
                        'currentProductAmount' => $commissionProduct->currentProductAmount,
                    ];
                }
            }
        }


        usort($latestProducts, function ($a, $b) {
            return $a['productId'] <=> $b['productId'];
        });


        foreach ($latestProducts as $index => &$product) {
            $product['id'] = $index + 1;
        }

        return response()->json(['data' => array_values($latestProducts)], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}


//% with amount
public function getDealerCommissionsAmount($dealerId)
{
    try {
        $cacheKey = 'dealer_commissions_' . $dealerId;

        $commissions = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($dealerId) {
            return Commission::where('dealerId', $dealerId)
                ->where('status', 'approved')
                ->with(['commissionProducts.product'])
                ->get();
        });

        $latestProducts = [];
        foreach ($commissions as $commission) {
            foreach ($commission->commissionProducts as $commissionProduct) {
                $productId = $commissionProduct->productId;

                if (!isset($latestProducts[$productId])) {
                    $basePrice = $commissionProduct->product->basePrice ?? 0;
                    $commissionPercentage = $commissionProduct->generalCommissionPercentagePerBag;
                    $commissionAmount = ($basePrice * $commissionPercentage) / 100;
                    $totalprice=($basePrice+$commissionProduct->incentiveCashBack)-($commissionAmount+$commissionProduct->cashIncentivePerBag+$commissionProduct->monthlyTargetPerBagCashAmount+$commissionProduct->yearlyTargetPerBagCashAmount+$commissionProduct->specialTargetPerBagCashAmount+$commissionProduct->perBagTransportDiscountAmount);
                    $latestProducts[$productId] = [
                        'commissionId' => $commission->id,
                        'productId' => $productId,
                        'productName' => $commissionProduct->product->shortName ?? 'N/A',
                        'basePrice' => $totalprice,
                        'generalCommissionPercentagePerBag' => $commissionProduct->generalCommissionPercentagePerBag,
                        'generalCommissionPercentagePerBagA' => "{$commissionAmount} ({$commissionPercentage}%)",
                        'cashIncentivePerBag' => $commissionProduct->cashIncentivePerBag,
                        'monthlyTargetQuantity' => $commissionProduct->monthlyTargetQuantity,
                        'monthlyTargetPerBagCashAmount' => $commissionProduct->monthlyTargetPerBagCashAmount,
                        'yearlyTargetQuantity' => $commissionProduct->yearlyTargetQuantity,
                        'yearlyTargetPerBagCashAmount' => $commissionProduct->yearlyTargetPerBagCashAmount,
                        'perBagTransportDiscountAmount' => $commissionProduct->perBagTransportDiscountAmount,
                        'specialTargetQuantity' => $commissionProduct->specialTargetQuantity,
                        'specialTargetPerBagCashAmount' => $commissionProduct->specialTargetPerBagCashAmount,
                        'incentiveCashBack' => $commissionProduct->incentiveCashBack,
                        'currentProductAmount' => $commissionProduct->currentProductAmount,
                    ];
                }
            }
        }



        usort($latestProducts, function ($a, $b) {
            return $a['productId'] <=> $b['productId'];
        });


        foreach ($latestProducts as $index => &$product) {
            $product['id'] = $index + 1;
        }

        return response()->json(['data' => array_values($latestProducts)], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}


// public function getDealerCommissionsAmount($dealerId, Request $request)
// {
//     try {
//         $commissionId = $request->input('commissionId'); // optional
//         $cacheKey = 'dealer_commissions_' . $dealerId . ($commissionId ? "_{$commissionId}" : '');

//         $commissions = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($dealerId, $commissionId) {
//             return Commission::where('dealerId', $dealerId)
//                 ->when($commissionId, function ($query) use ($commissionId) {
//                     $query->where('id', $commissionId);
//                 })
//                 ->where('status', 'approved')
//                 ->with(['commissionProducts.product'])
//                 ->orderByDesc('id') // get latest first
//                 ->get();
//         });

//         $latestProducts = [];

//         foreach ($commissions as $commission) {
//             foreach ($commission->commissionProducts as $commissionProduct) {
//                 $productId = $commissionProduct->productId;

//                 if (!isset($latestProducts[$productId])) {
//                     $basePrice = $commissionProduct->product->basePrice ?? 0;
//                     $commissionPercentage = $commissionProduct->generalCommissionPercentagePerBag;
//                     $commissionAmount = ($basePrice * $commissionPercentage) / 100;
//                     $totalPrice = ($basePrice + $commissionProduct->incentiveCashBack)
//                         - ($commissionAmount
//                         + $commissionProduct->cashIncentivePerBag
//                         + $commissionProduct->monthlyTargetPerBagCashAmount
//                         + $commissionProduct->yearlyTargetPerBagCashAmount
//                         + $commissionProduct->specialTargetPerBagCashAmount
//                         + $commissionProduct->perBagTransportDiscountAmount);

//                     $latestProducts[$productId] = [
//                         'productId' => $productId,
//                         'productName' => $commissionProduct->product->shortName ?? 'N/A',
//                         'basePrice' => $totalPrice,
//                         'generalCommissionPercentagePerBag' => $commissionPercentage,
//                         'generalCommissionPercentagePerBagA' => "{$commissionAmount} ({$commissionPercentage}%)",
//                         'cashIncentivePerBag' => $commissionProduct->cashIncentivePerBag,
//                         'monthlyTargetQuantity' => $commissionProduct->monthlyTargetQuantity,
//                         'monthlyTargetPerBagCashAmount' => $commissionProduct->monthlyTargetPerBagCashAmount,
//                         'yearlyTargetQuantity' => $commissionProduct->yearlyTargetQuantity,
//                         'yearlyTargetPerBagCashAmount' => $commissionProduct->yearlyTargetPerBagCashAmount,
//                         'perBagTransportDiscountAmount' => $commissionProduct->perBagTransportDiscountAmount,
//                         'specialTargetQuantity' => $commissionProduct->specialTargetQuantity,
//                         'specialTargetPerBagCashAmount' => $commissionProduct->specialTargetPerBagCashAmount,
//                         'incentiveCashBack' => $commissionProduct->incentiveCashBack,
//                         'currentProductAmount' => $commissionProduct->currentProductAmount,
//                     ];
//                 }
//             }
//         }

//         usort($latestProducts, function ($a, $b) {
//             return $a['productId'] <=> $b['productId'];
//         });

//         foreach ($latestProducts as $index => &$product) {
//             $product['id'] = $index + 1;
//         }

//         return response()->json(['data' => array_values($latestProducts)], 200);
//     } catch (\Exception $e) {
//         return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
//     }
// }

}