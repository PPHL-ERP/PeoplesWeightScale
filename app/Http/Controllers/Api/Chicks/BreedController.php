<?php

namespace App\Http\Controllers\Api\Chicks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chicks\BreedRequest;
use App\Http\Resources\Chicks\BreedResource;
use App\Models\Breed;
use Illuminate\Http\Request;

class BreedController extends Controller
{

    public function index()
    {
        $breeds = Breed::latest()->get();

        if($breeds->isEmpty()){
            return response()->json(['message' => 'No Breed found'], 200);
        }
        return BreedResource::collection($breeds);
    }

    public function store(BreedRequest $request)
    {
        $breed = Breed::create([
            'breedName' => $request->breedName,
            'note' => $request->note,
        ]);
        return response()->json([
            'message' => 'Breed created successfully',
            'data' => new BreedResource($breed),
        ],200);
    }


    public function show($id)
    {
        $breed = Breed::find($id);
        if (!$breed) {
            return response()->json(['message' => 'Breed not found'], 404);
        }
        return new BreedResource($breed);
    }


    public function update(BreedRequest $request, $id)
    {
        $breed = Breed::find($id);

        $breed->update([
            'breedName' => $request->breedName,
            'note' => $request->note,
        ]);

        return response()->json([
            'message' => 'Breed updated successfully',
            'data' => new BreedResource($breed),
        ],200);
    }


    public function destroy($id)
    {
        $breed = Breed::find($id);
        if (!$breed) {
            return response()->json(['message' => 'Breed not found'], 404);
        }

        $breed->delete();
        return response()->json([
            'message' => 'Breed deleted successfully',
        ],200);
    }
}