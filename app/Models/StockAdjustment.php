<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Relationships
     */

    // Each StockAdjustment belongs to a specific Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    // Each StockAdjustment was created by a specific user
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'crBy');
    }

    // Each StockAdjustment was approved by a specific user
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'appBy');
    }

    // Each StockAdjustment belongs to a specific Unit
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unitId');
    }

    // Each StockAdjustment belongs to a specific Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'productId');
    }

    // Each StockAdjustment might be related to a SalesEndpoint
    public function salesEndpoint()
    {
        return $this->belongsTo(SalesEndpoint::class, 'sales_endpoint_id');
    }
}
