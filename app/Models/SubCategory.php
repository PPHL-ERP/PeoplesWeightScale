<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Relationships
     */

    // Each SubCategory belongs to a specific Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'categoryId');
    }

    // Each SubCategory was created by a specific user
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'crBy');
    }

    // Each SubCategory was approved by a specific user
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'appBy');
    }

    // Each SubCategory can have many ChildCategories
    public function childCategories()
    {
        return $this->hasMany(ChildCategory::class, 'subCategoryId');
    }

    /**
     * Methods
     */

    // Method to update the status of the SubCategory and its ChildCategories
    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();

        foreach ($this->childCategories as $childCategory) {
            $childCategory->updateStatus($status);
        }
    }
}
