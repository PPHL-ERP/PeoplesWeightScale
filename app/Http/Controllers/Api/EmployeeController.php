<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
//use App\Exports\EmployeeExport;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
//use App\Imports\EmployeeImport;
use App\Models\Employee;
//use App\Models\EmployeeFacility;
// use App\Models\UserManagesSectors;
// use App\Traits\SectorFilter;
use App\Traits\UploadAble;
use Illuminate\Validation\ValidationException;
// use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use  UploadAble;

    // public function index()
    // {
    //     $employees = Employee::latest()->get();

    //     if($employees->isEmpty()){
    //         return response()->json(['message' => 'No Employee found'], 200);
    //     }
    //     return EmployeeResource::collection($employees);
    // }

  public function index(Request $request)
    {
      $emp_id = $request->emp_id ?? null;
      $full_name = $request->full_name ?? null;
      $phone_number = $request->phone_number ?? null;
      $email = $request->email ?? null;
      $startDate = $request->input('startDate');
      $endDate = $request->input('endDate');
      $status = $request->status ?? null;

      $query = Employee::query();

      if (!empty($emp_id)) {
        $query->where('emp_id', 'LIKE', '%' . $emp_id . '%');
      }

      if (!empty($full_name)) {
        $query->where(function ($query) use ($full_name) {
          $query->where('first_name', 'LIKE', '%' . $full_name . '%')
            ->orWhere('last_name', 'LIKE', '%' . $full_name . '%');
        });
      }

      if (!empty($phone_number)) {
        $query->where('phone_number', $phone_number);
      }

      if (!empty($email)) {
        $query->where('email', $email);
      }

      if (!empty($startDate) && !empty($endDate)) {
        $query->whereBetween('doj', [$startDate, $endDate]);
      }

      if (!empty($status)) {
        $query->where('status', $status);
      }

      $totalCount = $query->count();
      $limit = $totalCount > 50 ? 50 : $totalCount;
      $employees = $query->orderBy('id', 'desc')->paginate($limit);

      if ($employees->isEmpty()) {
        return response()->json([
          'message' => 'No Employee found',
          'data' => [],
        ], 200);
      }

      // Use the EmployeeResource to transform the data
      $transformedEmployees = EmployeeResource::collection($employees);

      return response()->json([
        'message' => 'Success!',
        'data' => $transformedEmployees,
        'totalCount' => $totalCount
      ], 200);
   }


    // public function employeeCount()
    // {
    //   $userid = auth()->user()->id;
    //   $userHasSectors = UserManagesSectors::where('userId', $userid)->pluck('sectorId')->toArray();

    //   $sectorsWithEmployeeCounts = Employee::whereHas('facility', function ($facilityQuery) use ($userHasSectors) {
    //     $facilityQuery->whereIn('sectorId', $userHasSectors);
    //   })->with('facility.sector')->get()->groupBy('facility.sector.name')->map(function ($employees) {
    //     return $employees->count();
    //   });

    //   if ($sectorsWithEmployeeCounts->isEmpty()) {
    //     return response()->json([
    //       'message' => 'Data not found'
    //     ], 200);
    //   } else {
    //     return response()->json([
    //       'Sector-wise Employee Count' => $sectorsWithEmployeeCounts
    //     ], 200);
    //   }
    // }
    // public function search(Request $request)
    // {
    //   $query = Employee::query();
    //   if ($request->has('search')) {
    //     $searchTerm = $request->input('search');
    //     $query->where(function ($q) use ($searchTerm) {
    //       $q->where('first_name', 'like', '%' . $searchTerm . '%')
    //         ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
    //         ->orWhere('email', 'like', '%' . $searchTerm . '%');
    //     });
    //   }

    //   $employees = $query->latest()->paginate(10);

    //   if ($employees->isEmpty()) {
    //     return response()->json(['message' => 'No employee found'], 200);
    //   }

    //   return EmployeeResource::collection($employees);
    // }



    public function store(EmployeeRequest $request)
    {
      try {
        $lastEmployee = Employee::orderBy('id', 'desc')->first();
        if ($lastEmployee) {
          $nextId = $lastEmployee->id + 1;
        } else {
          $nextId = 1;
        }
        $employee = new Employee();
        $employee->emp_id = 'emp-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        $employee->first_name = $request->first_name;
        $employee->last_name = $request->last_name;
        $employee->doj = $request->doj;
        if ($request->hasFile('image')) {
          $filename = $this->uploadOne($request->image, 300, 300, config('imagepath.employee'));
          $employee->image = $filename;    //update new filename
        }
        $employee->email = $request->email;
        $employee->phone_number = $request->phone_number;
        $employee->family_number = $request->family_number;
        $employee->nid = $request->nid;
        $employee->passport = $request->passport;
        $employee->dob = $request->dob;
        $employee->gender = $request->gender;
        $employee->marital_status = $request->marital_status;
        $employee->blood_group = $request->blood_group;
        $employee->current_address = $request->current_address;
        $employee->permanent_address = $request->permanent_address;
        $employee->status = 'pending';

        $employee->save();
        // dd($employee);
        return response()->json([
          'message' => 'Employee created successfully',
          'data' => new EmployeeResource($employee),
        ], 200);
      } catch (\Exception $e) {
        // Handle the exception here
        return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
      }
    }


    public function show($id)
    {
        //$employee = Employee::with(['facility', 'facility.company', 'facility.sector', 'facility.department', 'facility.designation', 'facility.shift'])->find($id);
        $employee = Employee::find($id);

        if (!$employee) {
          return response()->json(['message' => 'Employee not found'], 404);
        }
        return new EmployeeResource($employee);
       // return new EmployeeResource($employee, true);
    }


    public function update(EmployeeRequest $request, $id)
    {
      try {

        $employee = Employee::find($id);

        if (!$employee) {
          return $this->sendError('Employee not found.');
        }

        if ($request->hasFile('image')) {
          $filename = $this->uploadOne($request->image, 300, 300, config('imagepath.employee'));
          $this->deleteOne(config('imagepath.employee'), $employee->image);
          $employee->update(['image' => $filename]);
        } else {
          $employeeimg = Employee::find($id);
          $employee->image = $employeeimg->image;
        }
        $employee->first_name = $request->first_name;
        $employee->last_name = $request->last_name;
        $employee->doj = $request->doj;
        $employee->email = $request->email;
        $employee->phone_number = $request->phone_number;
        $employee->family_number = $request->family_number;
        $employee->nid = $request->nid;
        $employee->passport = $request->passport;
        $employee->dob = $request->dob;
        $employee->gender = $request->gender;
        $employee->marital_status = $request->marital_status;
        $employee->blood_group = $request->blood_group;
        $employee->current_address = $request->current_address;
        $employee->permanent_address = $request->permanent_address;
        $employee->status = 'pending';

        $employee->update();

        return response()->json([
          'message' => 'Employee Updated successfully',
          'data' => new EmployeeResource($employee),
        ], 200);
      } catch (\Exception $e) {
        // Handle the exception here
        return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
      }
    }

    public function statusUpdate(Request $request, $id)
    {
      $employee = Employee::find($id);
      $employee->status = $request->status;
      $employee->update();
      return response()->json([
        'message' => 'Employee Status change successfully',
      ], 200);
    }


    public function destroy($id)
    {
      $employee = Employee::find($id);
      if (!$employee) {
        return response()->json(['message' => 'Employee not found'], 404);
      }

      if ($employee->image) {
        $this->deleteOne(config('imagepath.employee'), $employee->image);
      }

      $employee->delete();
      return response()->json([
        'message' => 'Employee deleted successfully',
      ], 200);
    }


    // public function getEmpSectorIdShiftId(Request $request)
    // {
    //   $newDate = (new \DateTime())->format('Y-m-d');
    //   $currentDate = $request->date ? $request->date : $newDate;

    //   // First query facilities with shiftId
    //   $facilities = EmployeeFacility::where('shiftId', $request->shiftId)
    //     ->where('sectorId', $request->sectorId)
    //     ->select('id', 'employeeId', 'sectorId', 'shiftId', 'status')
    //     ->get();

    //   // Then query employees with employee.leave with the leave date condition above
    //   $employees = Employee::with(['leave' => function ($query) use ($currentDate) {
    //     $query->where('startDate', '<=', $currentDate)
    //       ->where('endDate', '>=', $currentDate)
    //       ->where('status', 'approved');
    //   }])->get(['id', 'emp_id', 'first_name', 'last_name', 'status', 'image']);

    //   // Create an associative array called employeesMap
    //   $employeesMap = [];
    //   foreach ($employees as $employee) {
    //     $employeesMap[$employee->id] = $employee;
    //   }

    //   // Associate each facility with its employee
    //   foreach ($facilities as $facility) {
    //     $facility->employee = $employeesMap[$facility->employeeId] ?? null;
    //   }

    //   return response()->json([
    //     'message' => 'Get Data successfully',
    //     'data' => $facilities
    //   ]);
    // }


    // public function importExcel(Request $request)
    // {
    //   //        dd($request->all());
    //   $this->validate($request, [
    //     'file'           => 'required|file|mimes:csv',
    //   ]);
    //   $file = $request->file('file');
    //   try {
    //     Excel::import(new EmployeeImport, $request->file('file'));
    //     return response()->json([
    //       'message' => 'data insert successfully!'
    //     ], 200);
    //   } catch (ValidationException $e) {
    //     return response()->json([
    //       'message' => 'There Are Some Error Please Check'
    //     ], 200);
    //   } catch (\Exception $e) {
    //     return response()->json([
    //       'message' => $e->getMessage()
    //     ], 403);
    //   }
    // }
    // public function exportExcel()
    // {
    //   return Excel::download(new EmployeeExport, 'employee.csv');
    // }

    public function getEmployeeList()
    {
      $approvedEmployees = Employee::where('status', 'approved')
        ->select('id', 'emp_id', 'first_name', 'last_name')
        ->get();
      return response()->json([
        'data' => $approvedEmployees
      ], 200);
    }
}
