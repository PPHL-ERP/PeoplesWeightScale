<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Relationships
     */

    // Each product belongs to a specific company
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    // Each product belongs to a specific category
    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryId');
    }

    // Each product belongs to a specific subcategory
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'subCategoryId');
    }

    // Each product belongs to a specific child category
    public function childCategory()
    {
        return $this->belongsTo(ChildCategory::class, 'childCategoryId');
    }

    // Each product has a unit of measure
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unitId');
    }

    /**
     * Workflow and Status
     */

    // Each product was created by a specific user
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'crBy');
    }

    // Each product was approved by a specific user
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'appBy');
    }

    /**
     * Accessors and Mutators (Optional)
     */
    // Add any custom accessors or mutators if needed for handling data transformation

    /**
     * Scopes (Optional)
     */
    // Add any query scopes if needed for filtering products


    //product unique code
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            $model->productId = 'PRO' . date('y') . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
        });
    }
}
