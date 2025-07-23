<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalesHasPaymentsRequest;
use App\Http\Resources\SalesHasPaymentsResource;
use App\Models\SalesHasPayments;
use Illuminate\Http\Request;

class SalesHasPaymentsController extends Controller
{

    public function index()
    {
        $sales_payments = SalesHasPayments::latest()->get();

        if($sales_payments->isEmpty()){
            return response()->json(['message' => 'No Sale Has Payments found'], 200);
        }
        return SalesHasPaymentsResource::collection($sales_payments);
    }


    public function store(SalesHasPaymentsRequest $request)
    {
        try {

            $sales_payment = new SalesHasPayments();

            $sales_payment->saleId = $request->saleId;
            $sales_payment->paymentTypeId = $request->paymentTypeId;
            $sales_payment->bankListId = $request->bankListId;
            $sales_payment->cashInfo = $request->cashInfo;
            $sales_payment->checkInfo = $request->checkInfo;

            $sales_payment->save();
            // dd($sales_payment);

            return response()->json([
              'message' => 'Sale Has Payments created successfully',
              'data' => new SalesHasPaymentsResource($sales_payment),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }    }


    public function show($id)
    {
        $sales_payment = SalesHasPayments::find($id);

        if (!$sales_payment) {
          return response()->json(['message' => 'Sale Has Payments not found'], 404);
        }
        return new SalesHasPaymentsResource($sales_payment);
    }

    public function update(SalesHasPaymentsRequest $request,$id)
    {
        try {

            $sales_payment = SalesHasPayments::find($id);

            if (!$sales_payment) {
              return $this->sendError('Sale Has Payments not found.');
            }

            $sales_payment->saleId = $request->saleId;
            $sales_payment->paymentTypeId = $request->paymentTypeId;
            $sales_payment->bankListId = $request->bankListId;
            $sales_payment->cashInfo = $request->cashInfo;
            $sales_payment->checkInfo = $request->checkInfo;

            $sales_payment->update();


            return response()->json([
              'message' => 'Sale Has Payments Updated successfully',
              'data' => new SalesHasPaymentsResource($sales_payment),
            ], 200);
          } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
          }
     }


    public function destroy($id)
    {
        $sales_payment = SalesHasPayments::find($id);
        if (!$sales_payment) {
          return response()->json(['message' => 'Sale Has Payments not found'], 404);
        }
        $sales_payment->delete();
        return response()->json([
          'message' => 'Sale Has Payments deleted successfully',
        ], 200);
   }
}
