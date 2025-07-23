<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesEndpoint extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Relationships
     */

    // Each SalesEndpoint belongs to a specific Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    // Each SalesEndpoint was created by a specific user
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'crBy');
    }

    // Each SalesEndpoint was approved by a specific user
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'appBy');
    }

    // Each SalesEndpoint can have many Inventories
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'sales_endpoint_id');
    }

    // Each SalesEndpoint can have many TransportLogs
    public function transportLogs()
    {
        return $this->hasMany(TransportLog::class, 'sales_endpoint_id');
    }

    // Each SalesEndpoint can have many SalesLogs
    public function salesLogs()
    {
        return $this->hasMany(SalesLog::class, 'sales_endpoint_id');
    }
}
