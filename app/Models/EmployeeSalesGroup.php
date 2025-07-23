<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalesGroup extends Model
{
    use HasFactory;
    protected $table = 'emp_sales_groups';
    protected $guarded = ['id'];

    public function managedEmployees()
{
    return $this->hasMany(EmployeeManageGroup::class, 'groupId', 'id');
}

}