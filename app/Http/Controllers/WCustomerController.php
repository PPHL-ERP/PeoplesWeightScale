<?php

namespace App\Http\Controllers;

use App\Http\Requests\WCustomerRequest;
use App\Models\WCustomer;
use Illuminate\Http\Request;

class WCustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = WCustomer::query();

        if ($s = $request->get('s')) {
            $q->where(function ($sub) use ($s) {
                $sub->where('cName', 'like', "%$s%")
                    ->orWhere('cId', 'like', "%$s%")
                    ->orWhere('phone', 'like', "%$s%");
            });
        }

        $customers = $q->latest()->paginate(12);
        $customers->appends($request->query());

        return view('w_customers.index', compact('customers'));
    }

    public function create()
    {
        return view('w_customers.create');
    }

    public function store(WCustomerRequest $request)
    {
        $customer = WCustomer::create($request->validated());

        return redirect()
            ->route('w_customers.show', $customer->id)
            ->with('success', 'Customer created successfully.');
    }

    public function show($id)
    {
        $customer = WCustomer::findOrFail($id);
        return view('w_customers.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = WCustomer::findOrFail($id);
        return view('w_customers.edit', compact('customer'));
    }

    public function update(WCustomerRequest $request, $id)
    {
        $customer = WCustomer::findOrFail($id);
        $customer->update($request->validated());

        return redirect()
            ->route('w_customers.show', $customer->id)
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy($id)
    {
        WCustomer::findOrFail($id)->delete(); // soft delete
        return redirect()
            ->route('w_customers.index')
            ->with('success', 'Customer deleted.');
    }
}