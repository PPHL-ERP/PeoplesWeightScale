<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Models\PaymentType;
class AllViewController extends Controller
{

 //     public function index(): JsonResponse
// {
//     $dealers = DB::select("SELECT * FROM dealer_view WHERE deleted_at IS NULL");

//     return response()->json([
//         'status' => 'success',
//         'data' => $dealers,
//     ]);
// }

public function productType()
{
    $data = DB::select("SELECT * FROM payment_types");

    return response()->json([
        'status' => 'success',
        'data' => $data
    ]);
}


public function dealerAllView(): JsonResponse
{
    $dealers = DB::select("
        SELECT
            dealer_id,
            \"dealerCode\",
            \"tradeName\",
            \"zoneId\",
            zone_name,
            \"divisionId\",
            division_name,
            \"districtId\",
            district_name,
            \"phone\",
            \"email\",
            \"tradeLicenseNo\",
            \"openingBalance\",
            \"salesPerson\",
            \"dealerGroup\",
            \"crBy\",
            createdbyname,
            \"status\"
        FROM dealer_view
        WHERE deleted_at IS NULL
    ");

    return response()->json([
        'status' => 'success',
        'data' => $dealers,
    ]);
}

//
public function sectorAllView(): JsonResponse
{
    $sectors = DB::select("SELECT * FROM sectors_view");

    return response()->json([
        'status' => 'success',
        'data' => $sectors,
    ]);
}


}
