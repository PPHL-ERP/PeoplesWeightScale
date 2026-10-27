# isSynced Column Addition - Summary

## Overview

Added a new `isSynced` boolean column to all sector-specific tables to enable synchronization status tracking between systems/devices.

## Changes Made

### 1. Database Schema Updates

**File**: `app/Console/Commands/CreateSectorTables.php`

Added `isSynced` column to both table creation methods:

```php
// In weight_transactions_{sector} tables
$table->boolean('isSynced')->default(false)->index();

// In transaction_images_{sector} tables
$table->boolean('isSynced')->default(false)->index();
```

**Properties**:
- Type: `boolean`
- Default: `false`
- Indexed: Yes (for performance when querying unsynced records)
- Location: Added just before `timestamps()`

### 2. Model Updates

**Files Modified**:
- `app/Models/WeightTransaction.php`
- `app/Models/TransactionImage.php`

Added `'isSynced'` to the `$fillable` array in both models, enabling mass assignment.

### 3. Validation Rules

**Files Modified**:
- `app/Http/Controllers/Api/WeightTransactionController.php`
  - Added to `store()` method's `$request->only()` array
  - Added to `update()` method's validation rules: `'isSynced' => 'nullable|boolean'`

- `app/Http/Requests/UploadImageRequest.php`
  - Added validation rule: `'isSynced' => 'nullable|boolean'`

- `app/Http/Controllers/Api/ImageUploadController.php`
  - Added to `uploadold()` method's create array
  - Added to `upload()` method's create array
  - Defaults to `false` if not provided: `'isSynced' => $data['isSynced'] ?? false`

### 4. Documentation Updates

**Files Updated**:
- `docs/SECTOR_SPECIFIC_STORAGE.md`
  - Updated database schema section
  - Added API examples with `isSynced` field
  - Added update sync status example

- `docs/IMPLEMENTATION_SUMMARY.md`
  - Added "Special Column: isSynced" section
  - Updated table creation features list
  - Added usage examples

- `docs/SECTOR_STORAGE_QUICK_REFERENCE.md`
  - Updated frontend requirements section
  - Added "Querying Unsynced Records" section with examples
  - Updated API consumers section

## Usage Examples

### Frontend - Creating Records

```javascript
// Create weight transaction with sync status
const response = await axios.post('/api/storeweight-transactions', {
  transaction_id: "TXN-12345",
  sector_id: 5,
  vehicle_no: "ABC-123",
  gross_weight: 1000,
  tare_weight: 200,
  isSynced: false  // Mark as not yet synced
});

// Upload image with sync status
const imageResponse = await axios.post('/api/v1/images/upload', {
  weighing_id: 123,
  sector_id: 5,
  camera_no: "CAM01",
  capture_datetime: "2025-10-21 14:30:00",
  image_base64: "...",
  isSynced: false  // Mark as not yet synced
});
```

### Frontend - Updating Sync Status

```javascript
// Mark single transaction as synced
await axios.put('/api/updateweight-transactions/123', {
  sector_id: 5,
  isSynced: true
});

// Mark multiple transactions as synced
const transactionIds = [123, 124, 125];
for (const id of transactionIds) {
  await axios.put(`/api/updateweight-transactions/${id}`, {
    sector_id: 5,
    isSynced: true
  });
}
```

### Backend - Querying Unsynced Records

```php
use App\Services\SectorModelResolver;
use App\Models\WeightTransaction;

$resolver = app(SectorModelResolver::class);

// Get unsynced transactions for specific sector
$modelClass = $resolver->getModelClass(WeightTransaction::class, $sectorId);
$unsyncedTransactions = $modelClass::where('isSynced', false)->get();

// Count unsynced records
$unsyncedCount = $modelClass::where('isSynced', false)->count();

// Get unsynced records older than 1 hour
$oneHourAgo = now()->subHour();
$oldUnsynced = $modelClass::where('isSynced', false)
    ->where('created_at', '<', $oneHourAgo)
    ->get();
```

### Backend - Bulk Update Sync Status

```php
// Mark all transactions in a batch as synced
$transactionIds = [1, 2, 3, 4, 5];
$modelClass::whereIn('id', $transactionIds)->update(['isSynced' => true]);

// Mark all records from a specific date as synced
$modelClass::whereDate('created_at', '2025-10-21')
    ->update(['isSynced' => true]);
```

### Backend - Sync Status Report

```php
use App\Models\Sector;

// Generate sync status report for all sectors
$syncReport = [];

foreach (Sector::all() as $sector) {
    try {
        $wtModelClass = $resolver->getModelClass(WeightTransaction::class, $sector->id);
        $imgModelClass = $resolver->getModelClass(TransactionImage::class, $sector->id);
        
        $syncReport[] = [
            'sector_id' => $sector->id,
            'sector_name' => $sector->name,
            'transactions_total' => $wtModelClass::count(),
            'transactions_unsynced' => $wtModelClass::where('isSynced', false)->count(),
            'images_total' => $imgModelClass::count(),
            'images_unsynced' => $imgModelClass::where('isSynced', false)->count(),
        ];
    } catch (\Exception $e) {
        // Sector tables might not exist
        \Log::warning("Sector {$sector->name} tables not found");
    }
}

return response()->json($syncReport);
```

## Migration Guide

### For Existing Sector Tables

If you've already created sector-specific tables before this update, you need to add the `isSynced` column:

```bash
# Option 1: Recreate tables (will lose data)
php artisan sector:create-tables --all --force

# Option 2: Add column manually via migration
php artisan make:migration add_is_synced_to_sector_tables
```

**Manual Migration Example**:

```php
// database/migrations/YYYY_MM_DD_HHMMSS_add_is_synced_to_sector_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Sector;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        foreach (Sector::all() as $sector) {
            $sanitizedName = preg_replace('/[^a-zA-Z0-9_\s]/', '', $sector->name);
            $tableSuffix = Str::snake(Str::studly($sanitizedName));
            
            // Add to weight_transactions table
            $wtTable = 'weight_transactions_' . $tableSuffix;
            if (Schema::hasTable($wtTable) && !Schema::hasColumn($wtTable, 'isSynced')) {
                Schema::table($wtTable, function (Blueprint $table) {
                    $table->boolean('isSynced')->default(false)->index();
                });
            }
            
            // Add to transaction_images table
            $imgTable = 'transaction_images_' . $tableSuffix;
            if (Schema::hasTable($imgTable) && !Schema::hasColumn($imgTable, 'isSynced')) {
                Schema::table($imgTable, function (Blueprint $table) {
                    $table->boolean('isSynced')->default(false)->index();
                });
            }
        }
    }
    
    public function down()
    {
        foreach (Sector::all() as $sector) {
            $sanitizedName = preg_replace('/[^a-zA-Z0-9_\s]/', '', $sector->name);
            $tableSuffix = Str::snake(Str::studly($sanitizedName));
            
            $wtTable = 'weight_transactions_' . $tableSuffix;
            if (Schema::hasTable($wtTable) && Schema::hasColumn($wtTable, 'isSynced')) {
                Schema::table($wtTable, function (Blueprint $table) {
                    $table->dropColumn('isSynced');
                });
            }
            
            $imgTable = 'transaction_images_' . $tableSuffix;
            if (Schema::hasTable($imgTable) && Schema::hasColumn($imgTable, 'isSynced')) {
                Schema::table($imgTable, function (Blueprint $table) {
                    $table->dropColumn('isSynced');
                });
            }
        }
    }
};
```

Run the migration:

```bash
php artisan migrate
```

## Use Cases

### 1. Offline-First Mobile App

```javascript
// Mobile app creates transaction while offline
const transaction = await localDB.save({
  transaction_id: "TXN-12345",
  sector_id: 5,
  vehicle_no: "ABC-123",
  isSynced: false  // Not yet sent to server
});

// When connection is restored, sync unsynced records
const unsyncedTransactions = await localDB.find({ isSynced: false });
for (const txn of unsyncedTransactions) {
  try {
    await axios.post('/api/storeweight-transactions', txn);
    await localDB.update(txn.id, { isSynced: true });
  } catch (error) {
    console.error('Sync failed:', error);
  }
}
```

### 2. Multi-Site Synchronization

```php
// Central server queries all unsynced data from regional sites
$unsyncedData = [];

foreach ($regionalSectors as $sector) {
    $modelClass = $resolver->getModelClass(WeightTransaction::class, $sector->id);
    $sectorUnsynced = $modelClass::where('isSynced', false)->get();
    
    foreach ($sectorUnsynced as $transaction) {
        $unsyncedData[] = $transaction->toArray();
    }
}

// Send to central database
Http::post('https://central.example.com/api/sync', [
    'data' => $unsyncedData,
    'source_site' => config('app.site_id')
]);

// Mark as synced
foreach ($regionalSectors as $sector) {
    $modelClass = $resolver->getModelClass(WeightTransaction::class, $sector->id);
    $modelClass::where('isSynced', false)->update(['isSynced' => true]);
}
```

### 3. Data Export/Backup

```php
// Export only unsynced data for backup
$exportData = [];

foreach (Sector::all() as $sector) {
    $wtModelClass = $resolver->getModelClass(WeightTransaction::class, $sector->id);
    $imgModelClass = $resolver->getModelClass(TransactionImage::class, $sector->id);
    
    $exportData[$sector->name] = [
        'transactions' => $wtModelClass::where('isSynced', false)->get(),
        'images' => $imgModelClass::where('isSynced', false)->get(),
    ];
}

Storage::put('backups/unsynced_' . date('Y-m-d_His') . '.json', json_encode($exportData));
```

## Testing

### Manual Testing Checklist

- [ ] Create new sector tables with `--force` flag
- [ ] Verify `isSynced` column exists in sector tables
- [ ] Create weight transaction with `isSynced: false`
- [ ] Create weight transaction with `isSynced: true`
- [ ] Create weight transaction without `isSynced` (should default to `false`)
- [ ] Update existing transaction to set `isSynced: true`
- [ ] Query unsynced transactions: `where('isSynced', false)`
- [ ] Upload image with `isSynced: false`
- [ ] Verify index on `isSynced` column exists

### SQL Verification

```sql
-- Check if isSynced column exists
DESCRIBE weight_transactions_dhaka_office;
DESCRIBE transaction_images_dhaka_office;

-- Check default value
SELECT COLUMN_NAME, COLUMN_DEFAULT 
FROM information_schema.COLUMNS 
WHERE TABLE_NAME = 'weight_transactions_dhaka_office' 
  AND COLUMN_NAME = 'isSynced';

-- Check index exists
SHOW INDEXES FROM weight_transactions_dhaka_office 
WHERE Column_name = 'isSynced';

-- Query unsynced records
SELECT COUNT(*) FROM weight_transactions_dhaka_office WHERE isSynced = 0;
SELECT COUNT(*) FROM transaction_images_dhaka_office WHERE isSynced = 0;
```

## Performance Considerations

1. **Index Added**: The `isSynced` column is indexed to optimize queries for unsynced records
2. **Query Optimization**: Use `where('isSynced', false)` instead of `where('isSynced', '=', false)` for better performance
3. **Bulk Updates**: Use `whereIn()` or batch updates instead of looping through individual records

## Important Notes

1. **Sector-Specific Only**: The `isSynced` column exists **only in sector-specific tables**, not in base tables
2. **Default Value**: Always defaults to `false` if not explicitly set
3. **Optional Field**: Frontend can omit this field; backend will default it to `false`
4. **Backward Compatible**: Existing code without `isSynced` continues to work
5. **Frontend Control**: The field can be updated from frontend via PUT requests

## Summary of Files Modified

### Created (1 file)
- `docs/IS_SYNCED_COLUMN.md` (this file)

### Modified (7 files)
- `app/Console/Commands/CreateSectorTables.php` - Added column to table schemas
- `app/Models/WeightTransaction.php` - Added to fillable array
- `app/Models/TransactionImage.php` - Added to fillable array
- `app/Http/Controllers/Api/WeightTransactionController.php` - Added to validation and data extraction
- `app/Http/Requests/UploadImageRequest.php` - Added validation rule
- `app/Http/Controllers/Api/ImageUploadController.php` - Added to both upload methods
- `docs/SECTOR_SPECIFIC_STORAGE.md` - Updated documentation
- `docs/IMPLEMENTATION_SUMMARY.md` - Updated documentation
- `docs/SECTOR_STORAGE_QUICK_REFERENCE.md` - Updated documentation

---

**Implementation Date**: October 21, 2025  
**Status**: ✅ Complete and Ready for Testing
