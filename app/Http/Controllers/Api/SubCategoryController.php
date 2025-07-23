<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubCategoryRequest;
use App\Http\Resources\SubCategoryResource;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{

    public function index()
    {
        $subcategories = SubCategory::latest()->get();

        if($subcategories->isEmpty()){
            return response()->json(['message' => 'No Sub Category found'], 200);
        }
        return SubCategoryResource::collection($subcategories);
    }


    public function store(SubCategoryRequest $request)
    {
        $subcategory = SubCategory::create([
            'subCategoryName' => $request->subCategoryName,
            'categoryId' => $request->categoryId,
            'description' => $request->description,
            'crBy' => auth()->id(),
            'status' => 'active',
        ]);
        return response()->json([
            'message' => 'Sub Category created successfully',
            'data' => new SubCategoryResource($subcategory),
        ],200);
    }


    public function show($id)
    {
        $subcategory = SubCategory::find($id);
        if (!$subcategory) {
            return response()->json(['message' => 'Sub Category not found'], 404);
        }
        return new SubCategoryResource($subcategory);
    }


    public function update(SubCategoryRequest $request, $id)
    {
        $subcategory = SubCategory::find($id);

        $subcategory->update([
            'subCategoryName' => $request->subCategoryName,
            'categoryId' => $request->categoryId,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Sub Category updated successfully',
            'data' => new SubCategoryResource($subcategory),
        ],200);
    }

    // public function statusUpdate(Request $request, $id)
    // {
    //     $subcategory = SubCategory::find($id);
    //     $subcategory->status = $request->status;
    //     $subcategory->appBy = auth()->id();

    //     $subcategory->update();
    //     return response()->json([
    //     'message' => 'SubCategory Status change successfully',
    //     ], 200);
    // }

    public function statusUpdate(Request $request, $id)
    {
        $subCategory = SubCategory::find($id);
        if (!$subCategory) {
            return response()->json(['message' => 'Sub Category not found'], 404);
        }

        $subCategory->updateStatus($request->status);
        $subCategory->appBy = auth()->id();
        $subCategory->update();

        return response()->json([
            'message' => 'SubCategory Status changed successfully',
        ], 200);
    }
    public function destroy($id)
    {
        $subcategory = SubCategory::find($id);
        if (!$subcategory) {
            return response()->json(['message' => 'Sub Category not found'], 404);
        }
        $subcategory->delete();
        return response()->json([
            'message' => 'Sub Category deleted successfully',
        ],200);
    }

    public function getSubActiveList()
    {
      $subActiveList = SubCategory::where('status', 'active')
        ->select('id', 'subCategoryName')
        ->get();
      return response()->json([
        'data' => $subActiveList
      ], 200);
    }
}