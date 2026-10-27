<?php

namespace App\Services;

use App\Models\Sector;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Service to dynamically resolve and create sector-specific model classes.
 * 
 * Given a base model class and sector identifier, returns a model class
 * that uses a sector-specific table name (e.g., weight_transactions_sector_name).
 */
class SectorModelResolver
{
    /**
     * Cache key prefix for sector lookups
     */
    private const SECTOR_CACHE_PREFIX = 'sector_model_resolver:sector:';
    
    /**
     * Cache TTL in seconds (1 hour)
     */
    private const CACHE_TTL = 3600;

    /**
     * Get sector-specific model class for a given base model.
     * 
     * @param string $baseModelClass Fully qualified base model class name
     * @param int|null $sectorId Sector ID
     * @param string|null $sectorName Sector name (used if sectorId not provided)
     * @return string Sector-specific model class name
     * @throws \InvalidArgumentException If sector cannot be resolved
     */
    public function getModelClass(string $baseModelClass, ?int $sectorId = null, ?string $sectorName = null): string
    {
        // Resolve sector name from ID if needed
        if ($sectorId && !$sectorName) {
            $sectorName = $this->resolveSectorName($sectorId);
        }

        if (!$sectorName) {
            throw new \InvalidArgumentException('Either sector_id or sector_name must be provided');
        }

        // Sanitize sector name for use in class and table names
        $sanitizedSectorName = $this->sanitizeSectorName($sectorName);

        // Generate sector-specific class name
        $baseClassName = class_basename($baseModelClass);
        $sectorSpecificClassName = $baseClassName . '_' . $sanitizedSectorName;
        $fullClassName = 'App\\Models\\SectorSpecific\\' . $sectorSpecificClassName;

        // Create the class dynamically if it doesn't exist
        if (!class_exists($fullClassName)) {
            $this->createSectorSpecificModel($fullClassName, $baseModelClass, $sanitizedSectorName);
        }

        return $fullClassName;
    }

    /**
     * Get an instance of the sector-specific model.
     * 
     * @param string $baseModelClass
     * @param int|null $sectorId
     * @param string|null $sectorName
     * @return Model
     */
    public function getModelInstance(string $baseModelClass, ?int $sectorId = null, ?string $sectorName = null): Model
    {
        $modelClass = $this->getModelClass($baseModelClass, $sectorId, $sectorName);
        return new $modelClass();
    }

    /**
     * Resolve sector name from sector ID.
     * 
     * @param int $sectorId
     * @return string
     * @throws \InvalidArgumentException If sector not found
     */
    private function resolveSectorName(int $sectorId): string
    {
        $cacheKey = self::SECTOR_CACHE_PREFIX . $sectorId;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($sectorId) {
            $sector = Sector::find($sectorId);
            
            if (!$sector) {
                throw new \InvalidArgumentException("Sector with ID {$sectorId} not found");
            }

            return $sector->name;
        });
    }

    /**
     * Sanitize sector name for use in class and table names.
     * Converts to PascalCase for class names, snake_case for table names.
     * 
     * @param string $sectorName
     * @return string
     */
    private function sanitizeSectorName(string $sectorName): string
    {
        // Remove special characters and convert to PascalCase
        $sanitized = preg_replace('/[^a-zA-Z0-9_\s]/', '', $sectorName);
        $sanitized = Str::studly($sanitized);
        
        return $sanitized;
    }

    /**
     * Convert sanitized sector name to snake_case for table names.
     * 
     * @param string $sanitizedName
     * @return string
     */
    private function sectorNameToTableSuffix(string $sanitizedName): string
    {
        return Str::snake($sanitizedName);
    }

    /**
     * Dynamically create a sector-specific model class.
     * 
     * @param string $fullClassName Fully qualified class name for the new model
     * @param string $baseModelClass Base model to extend from
     * @param string $sanitizedSectorName Sanitized sector name
     * @return void
     */
    private function createSectorSpecificModel(string $fullClassName, string $baseModelClass, string $sanitizedSectorName): void
    {
        // Get base model instance to copy properties
        $baseModel = new $baseModelClass();
        $baseTable = $baseModel->getTable();
        
        // Create sector-specific table name
        $sectorTableSuffix = $this->sectorNameToTableSuffix($sanitizedSectorName);
        $sectorTable = $baseTable . '_' . $sectorTableSuffix;

        // Extract namespace and class name
        $namespace = 'App\\Models\\SectorSpecific';
        $className = class_basename($fullClassName);

        // Create the class dynamically using eval
        // Note: In production, you might want to generate actual PHP files
        $classCode = <<<PHP
namespace {$namespace};

class {$className} extends \\{$baseModelClass}
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected \$table = '{$sectorTable}';
    
    /**
     * Indicates if this is a sector-specific model.
     *
     * @var bool
     */
    public \$isSectorSpecific = true;
    
    /**
     * The sector name this model is associated with.
     *
     * @var string
     */
    public \$sectorName = '{$sanitizedSectorName}';
}
PHP;

        eval($classCode);
    }

    /**
     * Get the table name for a sector-specific model without instantiating.
     * 
     * @param string $baseModelClass
     * @param int|null $sectorId
     * @param string|null $sectorName
     * @return string
     */
    public function getTableName(string $baseModelClass, ?int $sectorId = null, ?string $sectorName = null): string
    {
        if ($sectorId && !$sectorName) {
            $sectorName = $this->resolveSectorName($sectorId);
        }

        if (!$sectorName) {
            throw new \InvalidArgumentException('Either sector_id or sector_name must be provided');
        }

        $sanitizedSectorName = $this->sanitizeSectorName($sectorName);
        $sectorTableSuffix = $this->sectorNameToTableSuffix($sanitizedSectorName);
        
        $baseModel = new $baseModelClass();
        $baseTable = $baseModel->getTable();
        
        return $baseTable . '_' . $sectorTableSuffix;
    }
}
