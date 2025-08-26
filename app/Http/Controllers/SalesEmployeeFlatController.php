<?php
namespace App\Http\Controllers;

use App\Models\SalesEmployeeFlat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SalesEmployeeFlatController extends Controller
{
    public function index()
    {
        $data = DB::table('sales_employees_flat')
            ->select([
                'id',
                'employeeId',
                'employeeName',
                'phone_number',
                'companyName',
                'sectorName',
                'departmentName',
                'designationName',
                'status',
                'sGross',
                'jDate',
                'tLeave',
            ])
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employeeName' => 'required|string',
            'phone_number' => 'string',
            'companyName' => 'required|string',
            'sectorName' => 'nullable|string',
            'departmentName' => 'nullable|string',
            'designationName' => 'nullable|string',
            'status' => 'required|in:approved,pending,rejected',
            'sGross' => 'nullable|numeric',
            'jDate' => 'nullable|date',
            'tLeave' => 'nullable|integer',
        ]);

        $record = SalesEmployeeFlat::create($validated);

        return response()->json([
            'message' => 'Sales employee record created successfully',
            'data' => $record
        ], 201);
    }
}
