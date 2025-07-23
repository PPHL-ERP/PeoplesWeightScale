<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransportOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Relationships
     */

    // Origin SalesEndpoint
    public function originEndpoint()
    {
        return $this->belongsTo(SalesEndpoint::class, 'origin_sales_endpoint_id');
    }

    // Destination SalesEndpoint
    public function destinationEndpoint()
    {
        return $this->belongsTo(SalesEndpoint::class, 'destination_sales_endpoint_id');
    }

    // Product being transported
    public function product()
    {
        return $this->belongsTo(Product::class, 'productId');
    }

    // Company associated with the transport order
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    // User who created the transport order
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'crBy');
    }

    // User who approved the transport order
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'appBy');
    }
}
