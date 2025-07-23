<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Traits\UploadAble;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use UploadAble;

    public function index()
    {
        $companies = Company::latest()->get();

        if ($companies->isEmpty()) {
            return response()->json(['message' => 'No company found'], 200);
        }
        return CompanyResource::collection($companies);
    }


    public function store(CompanyRequest $request)
    {
        try {
            $company = new Company();

            $company->nameEn = $request->nameEn;
            $company->nameBn = $request->nameBn;
            $company->sloganEn = $request->sloganEn;
            $company->sloganBn = $request->sloganBn;
            $company->mobile = $request->mobile;
            $company->phone = $request->phone;
            $company->email = $request->email;
            $company->website = $request->website;

            if ($request->hasFile('image')) {
                $filename = $this->uploadOne($request->image, 300, 300, config('imagepath.company'));
                $company->image = $filename;    //update new filename

            }
            $company->tin = $request->tin;
            $company->bin = $request->bin;
            $company->addressEn = $request->addressEn;
            $company->addressBn = $request->addressBn;
            $company->comEx = $request->comEx;
            $company->status = 'approved';

            $company->save();

            return response()->json([
                'message' => 'Company created successfully',
                'data' => new CompanyResource($company),
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $company = Company::find($id);
        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        return new CompanyResource($company);
    }


    public function update(CompanyRequest $request, $id)
    {

        try {

            $company = Company::find($id);

            if (!$company) {
                return $this->sendError('Company not found.');
            }

            if ($request->hasFile('image')) {
                $filename = $this->uploadOne($request->image, 300, 300, config('imagepath.company'));
                $this->deleteOne(config('imagepath.company'), $company->image);
                $company->update(['image' => $filename]);
            }


            $company->nameEn = $request->nameEn;
            $company->nameBn = $request->nameBn;
            $company->sloganEn = $request->sloganEn;
            $company->sloganBn = $request->sloganBn;
            $company->mobile = $request->mobile;
            $company->phone = $request->phone;
            $company->email = $request->email;
            $company->website = $request->website;
            $company->tin = $request->tin;
            $company->bin = $request->bin;
            $company->addressEn = $request->addressEn;
            $company->addressBn = $request->addressBn;
            $company->comEx = $request->comEx;
            $company->status = $request->status;

            $company->update();

            return response()->json([
                'message' => 'Company Updated successfully',
                'data' => new CompanyResource($company),
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception here
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function statusUpdate(Request $request, $id)
    {
        $company = Company::find($id);
        $company->status = $request->status;
        $company->update();
        return response()->json([
            'message' => 'Company Status change successfully',
        ], 200);
    }
    public function destroy($id)
    {
        $company = Company::find($id);
        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        if ($company->image) {
            $this->deleteOne(config('imagepath.company'), $company->image);
        }
        $company->delete();
        return response()->json([
            'message' => 'Company deleted successfully',
        ], 200);
    }
}
