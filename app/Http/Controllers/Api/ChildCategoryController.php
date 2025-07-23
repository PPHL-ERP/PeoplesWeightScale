<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChildCategoryRequest;
use App\Http\Resources\ChildCategoryResource;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class ChildCategoryController extends Controller
{

    public function index()
    {
        $childCategories = ChildCategory::latest()->get();

        if($childCategories->isEmpty()){
            return response()->json(['message' => 'No Child Category found'], 200);
        }
        return ChildCategoryResource::collection($childCategories);
    }


    public function store(ChildCategoryRequest $request)
    {
        $childCategory = ChildCategory::create([
            'childCategoryName' => $request->childCategoryName,
            'subCategoryId' => $request->subCategoryId,
            'description' => $request->description,
            'crBy' => auth()->id(),
            'status' => 'active',
        ]);
        return response()->json([
            'message' => 'Child Category created successfully',
            'data' => new ChildCategoryResource($childCategory),
        ],200);
    }


    public function show($id)
    {
        $childCategory = ChildCategory::find($id);
        if (!$childCategory) {
            return response()->json(['message' => 'Child Category not found'], 404);
        }
        return new ChildCategoryResource($childCategory);
    }


    public function update(ChildCategoryRequest $request, $id)
    {
        $childCategory = ChildCategory::find($id);

        $childCategory->update([
            'childCategoryName' => $request->childCategoryName,
            'subCategoryId' => $request->subCategoryId,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Child Category updated successfully',
            'data' => new ChildCategoryResource($childCategory),
        ],200);
    }


    // public function statusUpdate(Request $request, $id)
    // {
    //     $childCategory = ChildCategory::find($id);
    //     $childCategory->status = $request->status;
    //     $childCategory->appBy = auth()->id();

    //     $childCategory->update();
    //     return response()->json([
    //     'message' => 'ChildCategory Status change successfully',
    //     ], 200);
    // }
    public function statusUpdate(Request $request, $id)
    {
        $childCategory = ChildCategory::find($id);
        if (!$childCategory) {
            return response()->json(['message' => 'Child Category not found'], 404);
        }

        $childCategory->updateStatus($request->status);
        $childCategory->appBy = auth()->id();
        $childCategory->update();

        return response()->json([
            'message' => 'ChildCategory Status changed successfully',
        ], 200);
    }
    public function destroy($id)
    {
        $childCategory = ChildCategory::find($id);
        if (!$childCategory) {
            return response()->json(['message' => 'Child Category not found'], 404);
        }
        $childCategory->delete();
        return response()->json([
            'message' => 'Child Category deleted successfully',
        ],200);
    }

    // public function getChildCateList()
    // {
    //   $childCateList = ChildCategory::where('status', 'active')
    //     ->select('id', 'childCategoryName')
    //     ->get();
    //   return response()->json([
    //     'data' => $childCateList
    //   ], 200);
    // }


// with validation category Egg
public function getChildCateList()
{
    // Get the Category ID for "Egg"
    $eggCategoryId = Category::where('name', 'Egg')->pluck('id')->first();

    if ($eggCategoryId) {
        $subCategoriesForEgg = SubCategory::where('categoryId', $eggCategoryId)->pluck('id');

        $childCateList = ChildCategory::where('status', 'active')
            ->whereIn('subCategoryId', $subCategoriesForEgg)
            ->with(['subCategory:id,subCategoryName'])
            ->select('id', 'childCategoryName', 'subCategoryId')
            ->get();

        $childCateList->transform(function ($childCategory) {
            $childCategory->subCategoryName = $childCategory->subCategory ? $childCategory->subCategory->subCategoryName : null;
            unset($childCategory->subCategoryId);
            return $childCategory;
        });

        return response()->json([
            'data' => $childCateList
        ], 200);
    } else {
        return response()->json([
            'message' => 'Egg category not found'
        ], 404);
    }
}


  // with subcategoryName
    public function getChildActiveList()
   {
    $childActiveList = ChildCategory::where('status', 'active')
        ->with(['subCategory:id,subCategoryName'])
        ->select('id', 'childCategoryName', 'subCategoryId')
        ->get();

    $childActiveList->transform(function ($childCategory) {
        $childCategory->subCategoryName = $childCategory->subCategory ? $childCategory->subCategory->subCategoryName : null;
        unset($childCategory->subCategoryId);
        return $childCategory;
    });

    return response()->json([
        'data' => $childActiveList
    ], 200);
   }


   // with validation category Feed
    public function getFeedChildCateList()
    {
        // Get the Category ID for "Feed"
        $feedCategoryId = Category::where('name', 'Feed')->pluck('id')->first();

        if ($feedCategoryId) {
            $subCategoriesForFeed = SubCategory::where('categoryId', $feedCategoryId)->pluck('id');

            $childCateList = ChildCategory::where('status', 'active')
                ->whereIn('subCategoryId', $subCategoriesForFeed)
                ->with(['subCategory:id,subCategoryName'])
                ->select('id', 'childCategoryName', 'subCategoryId')
                ->get();

            $childCateList->transform(function ($childCategory) {
                $childCategory->subCategoryName = $childCategory->subCategory ? $childCategory->subCategory->subCategoryName : null;
                unset($childCategory->subCategoryId);
                return $childCategory;
            });

            return response()->json([
                'data' => $childCateList
            ], 200);
        } else {
            return response()->json([
                'message' => 'Feed category not found'
            ], 404);
        }
    }


     // with validation category Chicks
     public function getChicksChildCateList()
     {
         // Get the Category ID for "Chicks"
         $chicksCategoryId = Category::where('name', 'Chicks')->pluck('id')->first();

         if ($chicksCategoryId) {
             $subCategoriesForChicks = SubCategory::where('categoryId', $chicksCategoryId)->pluck('id');

             $childCateList = ChildCategory::where('status', 'active')
                 ->whereIn('subCategoryId', $subCategoriesForChicks)
                 ->with(['subCategory:id,subCategoryName'])
                 ->select('id', 'childCategoryName', 'subCategoryId')
                 ->get();

             $childCateList->transform(function ($childCategory) {
                 $childCategory->subCategoryName = $childCategory->subCategory ? $childCategory->subCategory->subCategoryName : null;
                 unset($childCategory->subCategoryId);
                 return $childCategory;
             });

             return response()->json([
                 'data' => $childCateList
             ], 200);
         } else {
             return response()->json([
                 'message' => 'Chicks category not found'
             ], 404);
         }
     }

}