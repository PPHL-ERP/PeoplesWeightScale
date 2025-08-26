<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesEmployeeFlat extends Model
{
    protected $table = 'sales_employees_flat';

    protected $fillable = [
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
    ];
}

