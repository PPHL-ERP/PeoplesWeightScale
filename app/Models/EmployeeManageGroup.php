<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeManageGroup extends Model
{
    use HasFactory;
    protected $table = 'emp_manage_groups';
    protected $guarded = ['id'];
    protected $fillable = ['groupId', 'empId', 'userId'];

    public function empSalesGroup()
    {
        return $this->hasOne(EmployeeSalesGroup::class, 'id', 'groupId');
    }

    public function user() {
        return $this->hasOne(User::class, 'id', 'userId');
      }

}