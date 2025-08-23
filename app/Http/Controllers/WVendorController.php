<?php

namespace App\Http\Controllers;

use App\Http\Requests\WVendorRequest;
use App\Models\WVendor;
use Illuminate\Http\Request;

class WVendorController extends Controller
{
    public function index(Request $request)
    {
        $q = WVendor::query();

        if ($s = $request->get('s')) {
            $q->where(function($sub) use ($s) {
                $sub->where('vName', 'like', "%$s%")
                    ->orWhere('vId', 'like', "%$s%")
                    ->orWhere('phone', 'like', "%$s%");
            });
        }

        $vendors = $q->latest()->paginate(12);
        $vendors->appends($request->query());

        return view('w_vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('w_vendors.create');
    }

    public function store(WVendorRequest $request)
    {
        $data = $request->validated();
        $vendor = WVendor::create($data);

        return redirect()
            ->route('w_vendors.show', $vendor->id)
            ->with('success', 'Vendor created successfully.');
    }

    public function show($id)
    {
        $vendor = WVendor::findOrFail($id);
        return view('w_vendors.show', compact('vendor'));
    }

    public function edit($id)
    {
        $vendor = WVendor::findOrFail($id);
        return view('w_vendors.edit', compact('vendor'));
    }

    public function update(WVendorRequest $request, $id)
    {
        $vendor = WVendor::findOrFail($id);
        $vendor->update($request->validated());

        return redirect()
            ->route('w_vendors.show', $vendor->id)
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroy($id)
    {
        WVendor::findOrFail($id)->delete(); // soft delete
        return redirect()
            ->route('w_vendors.index')
            ->with('success', 'Vendor deleted.');
    }
}