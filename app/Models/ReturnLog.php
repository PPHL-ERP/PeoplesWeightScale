<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReturnLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Relationships
     */

    // Each ReturnLog belongs to a specific SalesLog
    public function salesLog()
    {
        return $this->belongsTo(SalesLog::class, 'sales_log_id');
    }

    // Each ReturnLog belongs to a specific Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'productId');
    }

    // Each ReturnLog belongs to a specific Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    // Each ReturnLog belongs to a specific SalesEndpoint
    public function salesEndpoint()
    {
        return $this->belongsTo(SalesEndpoint::class, 'sales_endpoint_id');
    }

    // Each ReturnLog was created by a specific user
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'crBy');
    }

    // Each ReturnLog was approved by a specific user
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'appBy');
    }
}
