<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SmsRequest;
use App\Http\Resources\SmsResource;
use App\Models\Sms;
use Illuminate\Http\Request;

class SmsController extends Controller
{

    public function index()
    {

        return SmsResource::collection(Sms::latest()->get());
    //     $sms = Sms::latest()->get();
    //     $smslast = SmsResource::collection($sms);

    //    return response()->json([
    //        'message' => 'Successful',
    //        'data' => $smslast
    //    ],200);
    }


    public function store(Request $request)
    {
        //
    }


    public function show(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        //
    }


    public function destroy(string $id)
    {
        //
    }
}
