<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function products()
    {
        return $this->hasMany(Product::class, 'companyId');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'companyId');
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'companyId');
    }

    public function childCategories()
    {
        return $this->hasMany(ChildCategory::class, 'companyId');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'companyId');
    }

    public function salesEndpoints()
    {
        return $this->hasMany(SalesEndpoint::class, 'companyId');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'companyId');
    }

    public function transportLogs()
    {
        return $this->hasMany(TransportLog::class, 'companyId');
    }

    public function salesLogs()
    {
        return $this->hasMany(SalesLog::class, 'companyId');
    }

    public function ledgers()
    {
        return $this->hasMany(Ledger::class, 'companyId');
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class, 'companyId');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'companyId');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'companyId');
    }
}
