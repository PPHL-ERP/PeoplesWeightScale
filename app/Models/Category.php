<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Relationships
     */

    // Each category belongs to a specific company
    public function company(){
        return $this->belongsTo(Company::class, 'companyId');
    }

    // Each category was created by a specific user
    public function createdBy() {
        return $this->belongsTo(User::class, 'crBy');
    }

    // Each category was approved by a specific user
    public function approvedBy() {
        return $this->belongsTo(User::class, 'appBy');
    }

    // Each category can have many subcategories
    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'categoryId');
    }

    /**
     * Methods
     */

    // Method to update the status of the Category and its SubCategories
    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();

        foreach ($this->subCategories as $subCategory) {
            $subCategory->updateStatus($status);
        }
    }
}
