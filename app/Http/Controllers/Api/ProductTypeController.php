<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use Illuminate\Http\Request;

class ProductTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $productTypes = ProductType::all();
            return response()->json(['data' => $productTypes], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve product types'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:product_types,name',
            'description' => 'nullable|string',
            'attributes' => 'nullable|json',
        ]);

        try {
            $productType = ProductType::create($validatedData);
            return response()->json(['data' => $productType], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create product type'], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $productType = ProductType::find($id);
            if (!$productType) {
                return response()->json(['error' => 'Product type not found'], 404);
            }
            return response()->json(['data' => $productType], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve product type'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:product_types,name,' . $id,
            'description' => 'nullable|string',
            'attributes' => 'nullable|json',
        ]);

        try {
            $productType = ProductType::find($id);
            if (!$productType) {
                return response()->json(['error' => 'Product type not found'], 404);
            }
            $productType->update($validatedData);
            return response()->json(['data' => $productType], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update product type'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $productType = ProductType::find($id);
            if (!$productType) {
                return response()->json(['error' => 'Product type not found'], 404);
            }
            $productType->delete();
            return response()->json(['message' => 'Product type deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete product type'], 500);
        }
    }
}
