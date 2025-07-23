<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentTypeRequest;
use App\Http\Resources\PaymentTypeResource;
use App\Models\PaymentType;
use Illuminate\Http\Request;

class PaymentTypeController extends Controller
{

    public function index()
    {
        $payment_types = PaymentType::latest()->get();

        if($payment_types->isEmpty()){
            return response()->json(['message' => 'No Payment Type found'], 200);
        }
        return PaymentTypeResource::collection($payment_types);
    }


    public function store(PaymentTypeRequest $request)
    {
        $payment_type = PaymentType::create([
            'name' => $request->name,
            'note' => $request->note,
        ]);
        return response()->json([
            'message' => 'Payment Type created successfully',
            'data' => new PaymentTypeResource($payment_type),
        ],200);
    }

    public function show($id)
    {
        $payment_type = PaymentType::find($id);
        if (!$payment_type) {
            return response()->json(['message' => 'Payment Type not found'], 404);
        }
        return new PaymentTypeResource($payment_type);
    }

    public function update(PaymentTypeRequest $request, $id)
    {
        $payment_type = PaymentType::find($id);

        $payment_type->update([
            'name' => $request->name,
            'note' => $request->note,
        ]);

        return response()->json([
            'message' => 'Payment Type updated successfully',
            'data' => new PaymentTypeResource($payment_type),
        ],200);
    }


    public function destroy($id)
    {
        $payment_type = PaymentType::find($id);
        if (!$payment_type) {
            return response()->json(['message' => 'Payment Type not found'], 404);
        }

        $payment_type->delete();
        return response()->json([
            'message' => 'Payment Type deleted successfully',
        ],200);
    }
}