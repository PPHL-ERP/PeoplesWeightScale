<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\UserActivity;
use App\Traits\CategoryFilter;


class CategoryController extends Controller
{

    use CategoryFilter;

    public function index()
    {
        $categories = Category::latest()->get();

        if($categories->isEmpty()){
            return response()->json(['message' => 'No Category found'], 200);
        }
        return CategoryResource::collection($categories);
    }


    public function store(CategoryRequest $request)
    {
        $category = Category::create([
            'name' => $request->name,
            'companyId' => $request->companyId,
            'note' => $request->note,
             'crBy' => auth()->id(),
             'status' => 'active',
        ]);
        $userActivity = UserActivity::create([
            'user_id' => auth()->user()->id,
            'module_name' => 'Category',
            'message' => 'New Category created successfully',
            'module_details' => json_encode([
              'name' => $category->name,
              'note' => $category->note,
            ]),
          ]);
        return response()->json([
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category),
        ],200);
    }


    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return new CategoryResource($category);
    }


    public function update(CategoryRequest $request, $id)
    {
        $category = Category::find($id);

        $category->update([
            'name' => $request->name,
            'companyId' => $request->companyId,
            'note' => $request->note,
        ]);
        $userActivity = UserActivity::create([
            'user_id' => auth()->user()->id,
            'module_name' => 'Category',
            'message' => 'Category Updated Successfully!',
          ]);
        return response()->json([
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category),
        ],200);
    }

    // public function statusUpdate(Request $request, $id)
    // {
    //     $category = Category::find($id);
    //     $category->status = $request->status;
    //     $category->appBy = auth()->id();

    //     $category->update();
    //     return response()->json([
    //     'message' => 'Category Status change successfully',
    //     ], 200);
    // }

    public function statusUpdate(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->updateStatus($request->status);
        $category->appBy = auth()->id();
        $category->update();
        return response()->json([
            'message' => 'Category Status changed successfully',
        ], 200);
    }


    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        $userActivity = UserActivity::create([
            'user_id' => auth()->user()->id,
            'module_name' => 'Category',
            'message' => 'Category Deleted Successfully!',
          ]);
        $category->delete();
        return response()->json([
            'message' => 'Category deleted successfully',
        ],200);
    }

    public function getCateList()
    {
        $query = Category::query(); // Start with the base query
    
        // Apply the category filter from the trait
        $query = $this->applyCategoryFilter($query);
    
        // Filter categories based on 'status' and select relevant fields
        $cateList = $query->where('status', 'active')
            ->select('id', 'name')
            ->get();
    
        return response()->json([
            'data' => $cateList
        ], 200);
    }
}
