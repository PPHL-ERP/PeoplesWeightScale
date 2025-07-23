<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\UserActivity;
use App\Services\ChicksStockService;
use App\Traits\UploadAble;
use App\Services\EggStockService;
use App\Services\FeedStockService;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // protected $eggStockService;

    // public function __construct(EggStockService $eggStockService)
    // {
    //     $this->eggStockService = $eggStockService;
    // }

    protected $eggStockService;
    protected $feedStockService;
    protected $chicksStockService;

    public function __construct(EggStockService $eggStockService, FeedStockService $feedStockService, ChicksStockService $chicksStockService)
    {
        $this->eggStockService = $eggStockService;
        $this->feedStockService = $feedStockService;
        $this->chicksStockService = $chicksStockService;
    }

    use  UploadAble;

    public function index(Request $request)
     {

      $productId = $request->productId ?? null;
      $productName = $request->productName ?? null;
      $categoryId = $request->categoryId ?? null;
      $subCategoryId = $request->subCategoryId ?? null;
      $childCategoryId = $request->childCategoryId ?? null;
      $status = $request->status ?? null;


      $query = Product::query();

      // Filter by productId
         if ($productId) {
        $query->where('productId', 'LIKE', '%' . $productId . '%');
      }

      //Filter by productName
      if ($productName) {
        $query->orWhere('productName', 'LIKE', '%' . $productName . '%');
      }


      // Filter by categoryId
      if ($categoryId) {
        $query->where('categoryId', $categoryId);
      }


      // Filter by subCategoryId
      if ($subCategoryId) {
        $query->where('subCategoryId', $subCategoryId);
      }

      // Filter by childCategoryId
      if ($childCategoryId) {
        $query->where('childCategoryId', $childCategoryId);
      }

      // Filter by status
      if ($status) {
        $query->where('status', $status);
      }

      // Fetch products with eager loading of related data
      $products = $query->latest()->get();

      // Check if any products found
      if ($products->isEmpty()) {
        return response()->json(['message' => 'No Product found', 'data' => []], 200);
      }

      // Use the ProductResource to transform the data
      $transformedProducts = ProductResource::collection($products);

      // Return products transformed with the resource
      return response()->json([
        'message' => 'Success!',
        'data' => $transformedProducts
      ], 200);
    }

    public function store(ProductRequest $request)
    {
        try {

          $product = new Product();

          $product->productId = $request->productId;
          $product->productName = $request->productName;
          $product->productType = $request->productType;
          $product->sn = $request->sn;
          $product->qrCode = $request->qrCode;
          $product->batchNo = $request->batchNo;
          $product->companyId = $request->companyId;
          $product->categoryId = $request->categoryId;
          $product->subCategoryId = $request->subCategoryId;
          $product->childCategoryId = $request->childCategoryId;
          $product->unitId = $request->unitId;

          if ($request->hasFile('image')) {
            $filename = $this->uploadOne($request->image, 300, 300, config('imagepath.product'));
            $product->image = $filename;    //update new filename
          }
          $product->basePrice = $request->basePrice;
          $product->sizeOrWeight = $request->sizeOrWeight;
          $product->shortName = $request->shortName;
          $product->productForm = $request->productForm;
          $product->warranty = $request->warranty;
          $product->minStock = $request->minStock;
          $product->description = $request->description;
          $product->crBy = auth()->id();
          $product->status = 'pending';

          $product->save();
          // dd($product);
          $userActivity = UserActivity::create([
            'user_id' => auth()->user()->id,
            'module_name' => 'Product',
            'message' => 'New Product created successfully',
            'module_details' => json_encode([
              'productName' => $product->productName,
              'basePrice' => $product->basePrice,
            ]),
          ]);
          return response()->json([
            'message' => 'Product created successfully',
            'data' => new ProductResource($product),
          ], 200);
        } catch (\Exception $e) {
          // Handle the exception here
          return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
      }


    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
          return response()->json(['message' => 'Product not found'], 404);
        }
        return new ProductResource($product);
    }


    public function update(ProductRequest $request, $id)
    {
        try {

          $product = Product::find($id);

          if (!$product) {
            return $this->sendError('Product not found.');
          }

          if ($request->hasFile('image')) {
            $filename = $this->uploadOne($request->image, 300, 300, config('imagepath.product'));
            $this->deleteOne(config('imagepath.product'), $product->image);
            $product->update(['image' => $filename]);
          } else {
            $productimg = Product::find($id);
            $product->image = $productimg->image;
          }
          $product->productName = $request->productName;
          $product->productType = $request->productType;
          $product->sn = $request->sn;
          $product->qrCode = $request->qrCode;
          $product->batchNo = $request->batchNo;
          $product->companyId = $request->companyId;
          $product->categoryId = $request->categoryId;
          $product->subCategoryId = $request->subCategoryId;
          $product->childCategoryId = $request->childCategoryId;
          $product->unitId = $request->unitId;
          $product->basePrice = $request->basePrice;
          $product->sizeOrWeight = $request->sizeOrWeight;
          $product->shortName = $request->shortName;
          $product->productForm = $request->productForm;
          $product->warranty = $request->warranty;
          $product->minStock = $request->minStock;
          $product->description = $request->description;
          $product->status = $request->status;

          $product->update();
          $userActivity = UserActivity::create([
            'user_id' => auth()->user()->id,
            'module_name' => 'Product',
            'message' => 'Product Updated Successfully!',
          ]);

          return response()->json([
            'message' => 'Product Updated successfully',
            'data' => new ProductResource($product),
          ], 200);
        } catch (\Exception $e) {
          // Handle the exception here
          return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
      }

      public function statusUpdate(Request $request, $id)
      {
        $product = Product::find($id);
        $product->status = $request->status;
        $product->appBy = auth()->id();

        $product->update();
        return response()->json([
          'message' => 'Product Status change successfully',
        ], 200);
      }

    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
          return response()->json(['message' => 'Product not found'], 404);
        }

        if ($product->image) {
          $this->deleteOne(config('imagepath.product'), $product->image);
        }
        $userActivity = UserActivity::create([
            'user_id' => auth()->user()->id,
            'module_name' => 'Product',
            'message' => 'Product Deleted Successfully!',
          ]);
        $product->delete();
        return response()->json([
          'message' => 'Product deleted successfully',
        ], 200);
      }

   //product,unit,category join
public function getProList()
{
    $proList = Product::where('status', 'approved')
        ->with(['unit:id,name', 'category:id,name'])
        ->select('id', 'productName', 'unitId', 'categoryId','shortName','batchNo')
        ->get();

    $proList->transform(function ($product) {
        $product->name = $product->unit->name ?? null;
        unset($product->unit);
        return $product;
    });

    return response()->json([
        'data' => $proList
    ], 200);
}

public function getProCateList()
{
    // Fetch products with related category, subcategory, and child category details
    $proCateList = Product::where('status', 'approved')
        ->with([
            'category' => function ($query) {
                $query->where('status', 'active');
            },
            'subCategory' => function ($query) {
                $query->where('status', 'active');
            },
            'childCategory' => function ($query) {
                $query->where('status', 'active');
            }
        ])
        ->select('id', 'productName', 'categoryId', 'subCategoryId', 'childCategoryId')
        ->get();

    // Group products by categoryId
    $groupedProducts = $proCateList->groupBy('categoryId')->map(function ($products, $categoryId) {
        return [
            'categoryId' => $categoryId,
            'categoryName' => $products->first()->category->name ?? null,
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'productName' => $product->productName,
                    'sub_category' => [
                        'id' => $product->subCategory->id ?? null,
                        'subCategoryName' => $product->subCategory->subCategoryName ?? null
                    ],
                    'child_category' => [
                        'id' => $product->childCategory->id ?? null,
                        'childCategoryName' => $product->childCategory->childCategoryName ?? null
                    ]
                ];
            }),
        ];
    });

    // Return a JSON response
    return response()->json([
        'data' => $groupedProducts
    ], 200);
}

//apply farm production

// public function getProductsByChildCategory($id) {
//     $products = Product::where('childCategoryId', $id)
//                        ->where('status', 'approved')
//                        ->select('id','productId', 'productName')
//                        ->get();

//     return response()->json([
//         'data' => $products
//     ], 200);
// }

public function getProductsByChildCategory($id, Request $request) {
    $sectorId = $request->sectorId;

    // Get products by child category
    $products = Product::where('childCategoryId', $id)
                       ->where('status', 'approved')
                       ->select('id', 'productId', 'productName','sizeOrWeight','shortName')
                       ->get();

    // Get stock data from getProductStocksByChildCategory
    $stockData = $this->getProductStocksByChildCategory($sectorId, $id);

    // Map stocks to products by productId
    $productsWithStock = $products->map(function ($product) use ($stockData) {
        $stock = $stockData->firstWhere('productId', $product->id);

        // Add stock data if sectorId and childCategoryId match, else set to 0
        $product->closingBalance = $stock ? $stock->closingBalance : 0;
        $product->lockquantity = $stock ? $stock->lockquantity : 0;

        return $product;
    });

    return response()->json([
        'data' => $productsWithStock
    ], 200);
}
private function getProductStocksByChildCategory($sectorId, $childCategoryId) {
    return $this->eggStockService->getLatestProductStocksByChildCategory($sectorId, $childCategoryId);
}
//
public function getProductStockByFilters($id, Request $request) {
    $sectorId = $request->sectorId;

    // Get products by child category and approved status
    $products = Product::where('childCategoryId', $id)
                       ->where('status', 'approved')
                       ->select('id', 'productId', 'productName', 'minStock','sizeOrWeight','shortName')
                       ->get();

    // Get stock data based on sectorId and childCategoryId
    $stockData = $this->getProductStocksByChildCategory($sectorId, $id);

    // Map products with stock data, if stock data is not found, set to 0
    $productsWithStock = $products->map(function ($product) use ($stockData) {
        // Find matching stock data for the current product
        $stock = $stockData->firstWhere('productId', $product->id);

        // Set stock details (closingBalance, lockquantity), defaulting to 0 if no stock data exists
        $product->closingBalance = $stock ? $stock->closingBalance : 0;
        $product->lockquantity = $stock ? $stock->lockquantity : 0;

        return $product;
    });

    // Return the data as JSON
    return response()->json([
        'data' => $productsWithStock
    ], 200);
}

//
// filtering childCategory to product
public function getChildCateProductApproveList()
{
    $childCateProList = Product::where('status', 'approved')
        ->with(['unit:id,name', 'childCategory:id,childCategoryName'])
        ->select('id', 'productName','sizeOrWeight','shortName', 'unitId', 'childCategoryId')
        ->get();

    $childCateProList->transform(function ($product) {
        $product->name = $product->unit->name ?? null;
        $product->childCategoryName = $product->childCategory->childCategoryName ?? null;
        $product->childCategoryId = $product->childCategory->id ?? null;
        unset($product->unit, $product->childCategory);

        return $product;
    });

    return response()->json([
        'data' => $childCateProList
    ], 200);
}

// Feed Sales stock production ledgers api
public function getFeedProductsByChildCategory($id, Request $request) {
    $sectorId = $request->sectorId;

    // Get products by child category
    $products = Product::where('childCategoryId', $id)
                       ->where('status', 'approved')
                       ->select('id', 'productId', 'productName','sizeOrWeight','shortName','batchNo')
                       ->get();

    // Get stock data from getFeedProductStocksByChildCategory
    $stockData = $this->getFeedProductStocksByChildCategory($sectorId, $id);

    // Map stocks to products by productId
    $productsWithStock = $products->map(function ($product) use ($stockData) {
        $stock = $stockData->firstWhere('productId', $product->id);

        // Add stock data if sectorId and childCategoryId match, else set to 0
        $product->closingBalance = $stock ? $stock->closingBalance : 0;
        $product->lockquantity = $stock ? $stock->lockquantity : 0;

        return $product;
    });

    return response()->json([
        'data' => $productsWithStock
    ], 200);
}


private function getFeedProductStocksByChildCategory($sectorId, $childCategoryId) {
    return $this->feedStockService->getFeedLatestProductStocksByChildCategory($sectorId, $childCategoryId);
}

//
public function getFeedProductStockByFilters($id, Request $request) {
    $sectorId = $request->sectorId;

    // Get products by child category and approved status
    $products = Product::where('childCategoryId', $id)
                       ->where('status', 'approved')
                       ->select('id', 'productId', 'productName', 'minStock','shortName','sizeOrWeight','batchNo')
                       ->get();

    // Get stock data based on sectorId and childCategoryId
    $stockData = $this->getFeedProductStocksByChildCategory($sectorId, $id);

    // Map products with stock data, if stock data is not found, set to 0
    $productsWithStock = $products->map(function ($product) use ($stockData) {
        // Find matching stock data for the current product
        $stock = $stockData->firstWhere('productId', $product->id);

        // Set stock details (closingBalance, lockquantity), defaulting to 0 if no stock data exists
        $product->closingBalance = $stock ? $stock->closingBalance : 0;
        $product->lockquantity = $stock ? $stock->lockquantity : 0;

        return $product;
    });

    // Return the data as JSON
    return response()->json([
        'data' => $productsWithStock
    ], 200);
}

//only feedCategory product show
public function getApprovedFeedProducts()
{
    try {
        $feedCategoryId = 5;  // Feed category ID


        $products = Product::where('categoryId', $feedCategoryId)
            ->where('status', 'approved')
            ->orderBy('id', 'asc')
            ->get(['id', 'productName', 'shortName','batchNo']);


        return response()->json(['data' => $products], 200);
    } catch (\Exception $e) {

        return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}

//only chicksCategory product show
public function getApproveChicksProducts()
{
    try {
        $chicksCategoryId = 4;  // Chicks category ID


        $products = Product::where('categoryId', $chicksCategoryId)
            ->where('status', 'approved')
            ->orderBy('id', 'asc')
            ->get(['id', 'productName', 'shortName','batchNo']);


        return response()->json(['data' => $products], 200);
    } catch (\Exception $e) {

        return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
    }
}


// chicks Sales stock production ledgers api
public function getChicksProductsByChildCategory($id, Request $request) {
    $sectorId = $request->sectorId;

    // Get products by child category
    $products = Product::where('childCategoryId', $id)
                       ->where('status', 'approved')
                       ->select('id', 'productId', 'productName','sizeOrWeight','shortName','batchNo')
                       ->get();

    // Get stock data from getChicksProductStocksByChildCategory
    $stockData = $this->getChicksProductStocksByChildCategory($sectorId, $id);

    // Map stocks to products by productId
    $productsWithStock = $products->map(function ($product) use ($stockData) {
        $stock = $stockData->firstWhere('productId', $product->id);

        // Add stock data if sectorId and childCategoryId match, else set to 0
        $product->closingBalance = $stock ? $stock->closingBalance : 0;

        return $product;
    });

    return response()->json([
        'data' => $productsWithStock
    ], 200);
}


private function getChicksProductStocksByChildCategory($sectorId, $childCategoryId) {
    return $this->chicksStockService->getChicksLatestProductStocksByChildCategory($sectorId, $childCategoryId);
}


public function getChicksProductStockByFilters($id, Request $request) {
    $sectorId = $request->sectorId;

    // Get products by child category and approved status
    $products = Product::where('childCategoryId', $id)
                       ->where('status', 'approved')
                       ->select('id', 'productId', 'productName', 'minStock','shortName','sizeOrWeight','batchNo')
                       ->get();

    // Get stock data based on sectorId and childCategoryId
    $stockData = $this->getChicksProductStocksByChildCategory($sectorId, $id);

    // Map products with stock data, if stock data is not found, set to 0
    $productsWithStock = $products->map(function ($product) use ($stockData) {
        // Find matching stock data for the current product
        $stock = $stockData->firstWhere('productId', $product->id);

        // Set stock details (closingBalance, lockquantity), defaulting to 0 if no stock data exists
        $product->closingBalance = $stock ? $stock->closingBalance : 0;

        return $product;
    });

    // Return the data as JSON
    return response()->json([
        'data' => $productsWithStock
    ], 200);
}

}