<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\DivisionResource;
use App\Http\Resources\UpazilaResource;
use App\Models\Country;
use App\Models\District;
use App\Models\Division;
use App\Models\Upazila;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function getAllCountries()
    {
        $countries = Country::orderBy('name', 'ASC')->get();
        return CountryResource::collection($countries);
    }

    public function getAllDivisions()
    {
        $divisions = Division::with('districtsWithUpazilas')->get();
        return DivisionResource::collection($divisions);
    }
    public function getOnlyDivisions()
    {
        $divisions = Division::orderBy('name', 'ASC')->get();
        return DivisionResource::collection($divisions);
    }

    public function getDistricts()
    {
        $districts = District::orderBy('name', 'ASC')->get();
        return DistrictResource::collection($districts);
    }
    public function getAllDistricts($division_id)
    {
        $districts = District::where('division_id', $division_id)->orderBy('name', 'ASC')->get();
        return DistrictResource::collection($districts);
    }

    public function getUpazilas()
    {
        $upazilas = Upazila::orderBy('name', 'ASC')->get();
        return UpazilaResource::collection($upazilas);
    }
    public function getAllUpazilas($district_id)
    {
        $upazilas = Upazila::where('district_id', $district_id)->orderBy('name', 'ASC')->get();
        return UpazilaResource::collection($upazilas);
    }
}
