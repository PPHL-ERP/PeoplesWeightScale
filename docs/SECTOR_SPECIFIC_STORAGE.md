# Sector-Specific Data Storage Implementation

## Overview

The weight scale backend has been modified to support sector-specific data storage. Instead of storing all weighing transactions and images in single tables, the system now dynamically routes data to sector-specific tables based on the `sector_id` or `sector_name` provided in API requests.

## Key Changes

### 1. Dynamic Model Resolution
A new service (`SectorModelResolver`) dynamically creates and manages sector-specific Eloquent models at runtime. These models:
- Inherit all properties and methods from base models
- Use sector-specific table names (e.g., `weight_transactions_sector_a`)
- Are cached for performance

### 2. Modified API Endpoints

#### POST `/storeweight-transactions`
- **Controller**: `App\Http\Controllers\Api\WeightTransactionController@store`
- **Behavior**: Routes data to sector-specific table when `sector_id` or `sector_name` is provided
- **Backward Compatibility**: Falls back to base `weight_transactions` table if no sector specified

#### PUT `/updateweight-transactions/{id}`
- **Controller**: `App\Http\Controllers\Api\WeightTransactionController@update`
- **Behavior**: Uses sector-specific model based on sector in request
- **Backward Compatibility**: Searches base table if sector not specified

#### POST `/v1/images/upload`
- **Controller**: `App\Http\Controllers\Api\ImageUploadController@upload`
- **Behavior**: Stores image metadata in sector-specific table
- **Important**: Image **files** continue to be stored in the same directory structure (`storage/app/public/pictures/{sector_id}/...`). Only the **database records** use sector-specific tables.

### 3. Table Naming Convention
- Base table: `weight_transactions`
- Sector-specific: `weight_transactions_{sector_name_snake_case}`
- Examples:
  - Sector "Dhaka Office" → `weight_transactions_dhaka_office`
  - Sector "Factory-A" → `weight_transactions_factory_a`

## Setup Instructions

### Step 1: Create Sector-Specific Tables

Use the artisan command to create tables for your sectors:

```bash
# Create tables for a specific sector (by ID)
php artisan sector:create-tables 1

# Create tables for a specific sector (by name)
php artisan sector:create-tables "Dhaka Office"

# Create tables for all sectors in the database
php artisan sector:create-tables --all

# Force recreation (drops existing tables)
php artisan sector:create-tables --all --force
```

This command will create two tables for each sector:
1. `weight_transactions_{sector_name}`
2. `transaction_images_{sector_name}`

### Step 2: Verify Table Creation

Check that tables were created successfully:

```bash
# MySQL
mysql> SHOW TABLES LIKE '%weight_transactions%';
mysql> SHOW TABLES LIKE '%transaction_images%';

# PostgreSQL
\dt *weight_transactions*
\dt *transaction_images*
```

### Step 3: Test API Endpoints

Test the modified endpoints with sector-specific payloads:

```bash
# Create a weight transaction for sector ID 5
curl -X POST http://your-api/api/storeweight-transactions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "transaction_id": "TXN-001",
    "sector_id": 5,
    "sector_name": "Dhaka Office",
    "vehicle_no": "ABC-123",
    "gross_weight": 1000,
    "tare_weight": 200,
    "material": "Rice",
    "customer_name": "Customer A",
    "isSynced": false
  }'

# Upload an image for the same sector
curl -X POST http://your-api/api/v1/images/upload \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "weighing_id": 1,
    "transaction_id": "TXN-001",
    "sector_id": 5,
    "camera_no": "CAM01",
    "capture_datetime": "2025-10-21 14:30:00",
    "image_base64": "iVBORw0KGgoAAAANSUhEUgAAAAUA...",
    "checksum": "abc123...",
    "isSynced": false
  }'

# Update sync status
curl -X PUT http://your-api/api/updateweight-transactions/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "sector_id": 5,
    "isSynced": true
  }'
```

## Technical Details

### SectorModelResolver Service

Located at: `app/Services/SectorModelResolver.php`

**Key Methods**:
- `getModelClass($baseModelClass, $sectorId, $sectorName)` - Returns sector-specific model class
- `getModelInstance($baseModelClass, $sectorId, $sectorName)` - Returns instantiated model
- `getTableName($baseModelClass, $sectorId, $sectorName)` - Returns table name without instantiation

**Caching**:
- Sector name lookups are cached for 1 hour
- Model classes are created once per request and reused

### Model Structure

Sector-specific models are created dynamically in the `App\Models\SectorSpecific` namespace:

```php
namespace App\Models\SectorSpecific;

class WeightTransaction_DhakaOffice extends \App\Models\WeightTransaction
{
    protected $table = 'weight_transactions_dhaka_office';
    public $isSectorSpecific = true;
    public $sectorName = 'DhakaOffice';
}
```

### Backward Compatibility

The system maintains full backward compatibility:

1. **No sector specified**: Data goes to base tables (`weight_transactions`, `transaction_images`)
2. **Existing data**: Remains in base tables and continues to work
3. **Mixed environments**: Can support both sector-specific and non-sector-specific clients

### Image Storage

**Important**: Only the database storage is sector-specific. Image files continue to use the existing path structure:

```
storage/app/public/pictures/{sector_id}/{date}/{weighing_id}/{filename}
```

This ensures:
- No changes to file storage logic
- Consistent file paths across all sectors
- Easy file system backups and management

## Database Schema

### weight_transactions_{sector}

Same schema as base `weight_transactions` table, plus:
- All transaction fields (vehicle, weights, prices, etc.)
- Customer/vendor references
- Sector references (for cross-reference)
- **`isSynced`** (boolean, default: false) - Track sync status, updatable from frontend
- Timestamps

### transaction_images_{sector}

Same schema as base `transaction_images` table, plus:
- Weighing/transaction references
- Image path and metadata
- Capture information
- Checksum for deduplication
- **`isSynced`** (boolean, default: false) - Track sync status, updatable from frontend
- Timestamps

**Important**: The `isSynced` field is only available in sector-specific tables, not in the base tables.

## Monitoring and Logging

The controllers log sector-specific model usage:

```php
Log::info('WeightTransaction.store: using sector-specific model', [
    'model' => $modelClass,
    'table' => $tableName,
    'sector_id' => $sectorId,
    'sector_name' => $sectorName,
]);
```

Check logs to verify sector routing:

```bash
tail -f storage/logs/laravel.log | grep "sector-specific"
```

## Troubleshooting

### Issue: Tables not created

**Solution**: Run the artisan command manually:
```bash
php artisan sector:create-tables --all
```

### Issue: Sector not found error

**Symptoms**: `InvalidArgumentException: Sector with ID X not found`

**Solution**: Verify the sector exists in the database:
```sql
SELECT id, name FROM sectors WHERE id = X;
```

### Issue: Data going to wrong table

**Solution**: Check the payload includes `sector_id` or `sector_name`:
```bash
# Enable debug logging
tail -f storage/logs/laravel.log
```

### Issue: Images not appearing

**Check**:
1. Image files are stored (check `storage/app/public/pictures/`)
2. Database record exists in correct table
3. `image_path` column matches actual file path

## Performance Considerations

1. **Model Creation**: Models are created dynamically using `eval()`. In high-traffic scenarios, consider pre-generating model files.

2. **Sector Name Caching**: Sector names are cached for 1 hour. Clear cache if sectors are renamed:
```bash
php artisan cache:clear
```

3. **Database Indexing**: All sector-specific tables have the same indexes as base tables for optimal performance.

## Migration Path

For existing deployments:

### Option 1: Gradual Migration
1. Create sector-specific tables
2. Clients start sending `sector_id` in new requests
3. New data goes to sector tables
4. Old data remains in base tables
5. Optionally migrate old data later

### Option 2: Full Migration
1. Create sector-specific tables
2. Migrate existing data:
```bash
# Example migration script
php artisan tinker
>>> WeightTransaction::chunk(1000, function($transactions) {
      foreach ($transactions as $t) {
        if ($t->sector_id) {
          // Move to sector-specific table
          DB::table('weight_transactions_' . $sectorSuffix)->insert($t->toArray());
        }
      }
    });
```
3. Update all clients to send `sector_id`

## Testing

A test suite should verify:
1. ✓ Sector-specific data storage
2. ✓ Backward compatibility with non-sector requests
3. ✓ Image file paths remain unchanged
4. ✓ Deduplication works across sector tables
5. ✓ Update operations find records in sector tables

Run tests:
```bash
php artisan test --filter WeightTransaction
php artisan test --filter ImageUpload
```

## Future Enhancements

1. **Pre-generated Models**: Generate actual PHP files for each sector instead of using `eval()`
2. **Multi-tenancy**: Extend to support complete database separation per sector
3. **Data Archival**: Automatic archival of old data from sector tables
4. **Reporting**: Aggregated reports across all sector tables

## Support

For questions or issues:
1. Check logs: `storage/logs/laravel.log`
2. Verify sector configuration in database
3. Test with a single sector first before using `--all`

---

**Last Updated**: October 21, 2025
**Version**: 1.0.0
