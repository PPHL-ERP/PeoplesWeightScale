<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Relationships
     */

    // Each Unit belongs to a specific Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    // Each Unit was created by a specific user
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'crBy');
    }

    // Each Unit was approved by a specific user
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'appBy');
    }

    // If Units are related to Products (for example, as units of measure)
    public function products()
    {
        return $this->hasMany(Product::class, 'unitId');
    }
}
