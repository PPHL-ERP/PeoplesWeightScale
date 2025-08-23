<?php

namespace App\Http\Controllers;

use App\Http\Requests\WMaterialRequest;
use App\Models\WMaterial;
use Illuminate\Http\Request;

class WMaterialController extends Controller
{
    public function index(Request $request)
    {
        $q = WMaterial::query();

        if ($s = $request->get('s')) {
            $q->where(function ($sub) use ($s) {
                $sub->where('mName', 'like', "%$s%")
                    ->orWhere('mId', 'like', "%$s%")
                    ->orWhere('categoryType', 'like', "%$s%");
            });
        }

        $materials = $q->latest()->paginate(12);
        $materials->appends($request->query());

        return view('w_materials.index', compact('materials'));
    }

    public function create()
    {
        return view('w_materials.create');
    }

    public function store(WMaterialRequest $request)
    {
        $material = WMaterial::create($request->validated());

        return redirect()
            ->route('w_materials.show', $material->id)
            ->with('success', 'Material created successfully.');
    }

    public function show($id)
    {
        $material = WMaterial::findOrFail($id);
        return view('w_materials.show', compact('material'));
    }

    public function edit($id)
    {
        $material = WMaterial::findOrFail($id);
        return view('w_materials.edit', compact('material'));
    }

    public function update(WMaterialRequest $request, $id)
    {
        $material = WMaterial::findOrFail($id);
        $material->update($request->validated());

        return redirect()
            ->route('w_materials.show', $material->id)
            ->with('success', 'Material updated successfully.');
    }

    public function destroy($id)
    {
        WMaterial::findOrFail($id)->delete(); // soft delete
        return redirect()
            ->route('w_materials.index')
            ->with('success', 'Material deleted.');
    }
}