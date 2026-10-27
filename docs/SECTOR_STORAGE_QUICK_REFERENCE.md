# Quick Reference: Sector-Specific Storage

## For Developers

### Creating Tables for a New Sector

When a new sector is added to the system:

```bash
# Create tables for sector ID 10
php artisan sector:create-tables 10

# Or by name
php artisan sector:create-tables "New Branch Office"
```

### Frontend Requirements

Ensure your API requests include `sector_id` or `sector_name`:

```javascript
// Weight transaction creation
const payload = {
  transaction_id: "TXN-12345",
  sector_id: 5,              // ← Required for sector-specific storage
  sector_name: "Dhaka Office", // ← Alternative to sector_id
  vehicle_no: "ABC-123",
  gross_weight: 1000,
  tare_weight: 200,
  isSynced: false,           // ← Optional: track sync status
  // ... other fields
};

// Image upload
const imagePayload = {
  weighing_id: 42,
  sector_id: 5,              // ← Required for sector-specific storage
  camera_no: "CAM01",
  capture_datetime: "2025-10-21 14:30:00",
  image_base64: "...",
  isSynced: false,           // ← Optional: track sync status
  // ... other fields
};

// Update sync status
const updatePayload = {
  sector_id: 5,
  isSynced: true,            // ← Mark as synced
};
```

### Checking Which Table Data Went To

```php
// In tinker or custom code
use App\Services\SectorModelResolver;

$resolver = app(SectorModelResolver::class);

// Get table name for sector ID 5
$tableName = $resolver->getTableName(
    \App\Models\WeightTransaction::class,
    5  // sector_id
);
// Returns: "weight_transactions_dhaka_office"

// Get model class
$modelClass = $resolver->getModelClass(
    \App\Models\WeightTransaction::class,
    5
);
// Returns: "App\Models\SectorSpecific\WeightTransaction_DhakaOffice"
```

### Querying Sector-Specific Data

```php
// Option 1: Use the resolver
$resolver = app(SectorModelResolver::class);
$modelClass = $resolver->getModelClass(WeightTransaction::class, $sectorId);
$transactions = $modelClass::where('vehicle_no', 'ABC-123')->get();

// Option 2: Direct table query
$tableName = 'weight_transactions_dhaka_office';
$transactions = DB::table($tableName)->where('vehicle_no', 'ABC-123')->get();
```

### Querying Unsynced Records

The `isSynced` column allows you to track and query records that haven't been synchronized:

```php
// Get all unsynced transactions for a specific sector
$resolver = app(SectorModelResolver::class);
$modelClass = $resolver->getModelClass(WeightTransaction::class, $sectorId);
$unsyncedTransactions = $modelClass::where('isSynced', false)->get();

// Get count of unsynced records
$unsyncedCount = $modelClass::where('isSynced', false)->count();

// Mark multiple records as synced
$modelClass::whereIn('id', [1, 2, 3])->update(['isSynced' => true]);

// Get unsynced records from all sectors
$allUnsynced = collect();
foreach (Sector::all() as $sector) {
    try {
        $modelClass = $resolver->getModelClass(WeightTransaction::class, $sector->id);
        $sectorUnsynced = $modelClass::where('isSynced', false)->get();
        $allUnsynced = $allUnsynced->merge($sectorUnsynced);
    } catch (\Exception $e) {
        \Log::warning("Could not query sector: " . $sector->name);
    }
}
```

### Adding Support in Reports

When building reports that need to aggregate across sectors:

```php
use App\Models\Sector;
use App\Services\SectorModelResolver;

$resolver = app(SectorModelResolver::class);
$allTransactions = collect();

// Iterate through all sectors
foreach (Sector::all() as $sector) {
    try {
        $modelClass = $resolver->getModelClass(
            WeightTransaction::class,
            $sector->id
        );
        
        $sectorTransactions = $modelClass::whereBetween('created_at', [$from, $to])->get();
        $allTransactions = $allTransactions->merge($sectorTransactions);
    } catch (\Exception $e) {
        // Sector table might not exist yet
        \Log::warning("Sector table not found: " . $sector->name);
    }
}

// Also check base table for non-sector data
$baseTransactions = WeightTransaction::whereBetween('created_at', [$from, $to])->get();
$allTransactions = $allTransactions->merge($baseTransactions);
```

## For DBAs

### Listing All Sector Tables

```sql
-- MySQL
SHOW TABLES LIKE 'weight_transactions_%';
SHOW TABLES LIKE 'transaction_images_%';

-- PostgreSQL
SELECT tablename FROM pg_tables 
WHERE tablename LIKE 'weight_transactions_%' 
   OR tablename LIKE 'transaction_images_%';
```

### Table Sizes

```sql
-- MySQL
SELECT 
    table_name,
    ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND (table_name LIKE 'weight_transactions_%' 
       OR table_name LIKE 'transaction_images_%')
ORDER BY size_mb DESC;

-- PostgreSQL
SELECT
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size
FROM pg_tables
WHERE tablename LIKE 'weight_transactions_%' 
   OR tablename LIKE 'transaction_images_%'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
```

### Backup Strategy

```bash
# Backup all sector tables
mysqldump -u user -p database_name \
  --tables $(mysql -u user -p -D database_name -e \
    "SHOW TABLES LIKE 'weight_transactions_%'" -sN) \
  > sector_tables_backup.sql

# Or backup specific sector
mysqldump -u user -p database_name \
  weight_transactions_dhaka_office \
  transaction_images_dhaka_office \
  > dhaka_office_backup.sql
```

### Index Monitoring

```sql
-- Check if indexes exist on sector tables
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME
FROM information_schema.STATISTICS
WHERE TABLE_NAME LIKE 'weight_transactions_%'
ORDER BY TABLE_NAME, INDEX_NAME;
```

## For API Consumers

### Required Fields

| Endpoint | Required for Sector Storage |
|----------|----------------------------|
| POST /storeweight-transactions | `sector_id` OR `sector_name` |
| PUT /updateweight-transactions/{id} | `sector_id` OR `sector_name` |
| POST /v1/images/upload | `sector_id` (from transaction) |

**Optional Fields**:
- `isSynced` (boolean): Track synchronization status (default: `false`)

### Response Format

Response format remains unchanged. The system transparently routes to sector tables:

```json
{
  "status": "created",
  "data": {
    "id": 123,
    "transaction_id": "TXN-12345",
    "sector_id": 5,
    "sector_name": "Dhaka Office",
    // ... other fields
  }
}
```

### Error Handling

| Error | Cause | Solution |
|-------|-------|----------|
| `Sector with ID X not found` | Invalid sector_id | Verify sector exists in database |
| `Table doesn't exist` | Sector table not created | Run `php artisan sector:create-tables X` |
| `Either sector_id or sector_name must be provided` | Missing both fields | Add sector identifier to request |

## For System Administrators

### Monitoring Commands

```bash
# Check if sector tables exist
php artisan tinker
>>> use Illuminate\Support\Facades\Schema;
>>> Schema::hasTable('weight_transactions_dhaka_office');
true

# List all sector tables
php artisan tinker
>>> DB::select("SHOW TABLES LIKE 'weight_transactions_%'");

# Get table row counts
php artisan tinker
>>> DB::table('weight_transactions_dhaka_office')->count();
```

### Performance Monitoring

```sql
-- Query performance by table
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    AVG_ROW_LENGTH,
    DATA_LENGTH
FROM information_schema.TABLES
WHERE TABLE_NAME LIKE 'weight_transactions_%'
ORDER BY TABLE_ROWS DESC;
```

### Cache Management

```bash
# Clear sector name cache if sectors are renamed
php artisan cache:clear

# Or clear specific sector cache
php artisan tinker
>>> Cache::forget('sector_model_resolver:sector:5');
```

## Common Tasks

### Task 1: Add New Sector Support

1. Add sector in database (via admin panel or SQL)
2. Create tables: `php artisan sector:create-tables {sector_id}`
3. Verify: Check database for new tables
4. Test: Send API request with new sector_id

### Task 2: Migrate Existing Data to Sector Tables

```php
// Example migration script
use App\Models\WeightTransaction;
use App\Services\SectorModelResolver;

$resolver = app(SectorModelResolver::class);

WeightTransaction::whereNotNull('sector_id')
    ->chunk(1000, function ($transactions) use ($resolver) {
        foreach ($transactions as $transaction) {
            try {
                $modelClass = $resolver->getModelClass(
                    WeightTransaction::class,
                    $transaction->sector_id
                );
                
                // Insert into sector table
                $modelClass::create($transaction->toArray());
                
                // Optionally delete from base table
                // $transaction->delete();
                
            } catch (\Exception $e) {
                \Log::error("Failed to migrate transaction {$transaction->id}: " . $e->getMessage());
            }
        }
    });
```

### Task 3: Archive Old Sector Data

```bash
# Export old data
mysqldump -u user -p database_name \
  --where="created_at < '2024-01-01'" \
  weight_transactions_dhaka_office \
  > archive_2023_dhaka_office.sql

# Delete from table (after backup verification)
mysql -u user -p database_name \
  -e "DELETE FROM weight_transactions_dhaka_office WHERE created_at < '2024-01-01'"
```

## Troubleshooting Quick Fixes

### "Class not found" error
```bash
composer dump-autoload
php artisan config:cache
```

### "Table doesn't exist" error
```bash
php artisan sector:create-tables {sector_id}
```

### Data not appearing in expected table
```bash
# Check logs
tail -f storage/logs/laravel.log | grep sector

# Verify sector_id in request
# Verify table exists
```

### Slow queries
```sql
-- Add missing indexes (if needed)
ALTER TABLE weight_transactions_dhaka_office 
  ADD INDEX idx_created_at (created_at);
```

---

**Quick Help**: Run `php artisan sector:create-tables --help` for command options.
