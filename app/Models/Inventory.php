<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Relationships
     */

    // Each Inventory belongs to a specific Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    // Each Inventory was created by a specific user
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'crBy');
    }

    // Each Inventory was approved by a specific user
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'appBy');
    }

    // Each Inventory belongs to a specific Unit
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unitId');
    }

    // Each Inventory belongs to a specific Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'productId');
    }

    // If Inventories are related to SalesEndpoints
    public function salesEndpoint()
    {
        return $this->belongsTo(SalesEndpoint::class, 'sales_endpoint_id');
    }

    /**
     * Accessors and Mutators (Optional)
     */
    // If you need custom accessors or mutators for in_transit_quantity or quantity

    /**
     * Scopes (Optional)
     */
    // Add any query scopes if needed for filtering inventories

    /**
     * Additional Relationships (Optional)
     */

    // If you need to track which transport orders affect this inventory
    public function transportOrders()
    {
        return $this->hasMany(TransportOrder::class, 'inventory_id');
    }

    /**
     * Attributes
     */

    // If we're using $fillable instead of $guarded
    // protected $fillable = [
    //     'unitId',
    //     'productId',
    //     'companyId',
    //     'sales_endpoint_id',
    //     'quantity',
    //     'in_transit_quantity',
    //     'date',
    //     'crBy',
    //     'appBy',
    // ];

    /**
     * Methods
     */

    // Method to calculate available quantity (excluding in_transit_quantity)
    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->in_transit_quantity;
    }
}
