<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Relationships
     */

    // Each SalesLog belongs to a specific Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    // Each SalesLog was created by a specific user
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'crBy');
    }

    // Each SalesLog was approved by a specific user
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'appBy');
    }

    // Each SalesLog belongs to a specific Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'productId');
    }

    // Each SalesLog belongs to a specific SalesEndpoint
    public function salesEndpoint()
    {
        return $this->belongsTo(SalesEndpoint::class, 'sales_endpoint_id');
    }
}
