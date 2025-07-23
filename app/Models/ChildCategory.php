<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChildCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Relationships
     */

    // Each ChildCategory belongs to a specific SubCategory
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'subCategoryId');
    }

    // Each ChildCategory was created by a specific user
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'crBy');
    }

    // Each ChildCategory was approved by a specific user
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'appBy');
    }

    /**
     * Methods
     */

    // Method to update the status of the ChildCategory
    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();
    }
}
