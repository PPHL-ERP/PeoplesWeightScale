<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Sector;

class CreateSectorTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sector:create-tables 
                            {sector? : Sector ID or name to create tables for}
                            {--all : Create tables for all sectors}
                            {--force : Force creation even if tables exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sector-specific tables for weight transactions and images';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $all = $this->option('all');
        $force = $this->option('force');
        $sectorInput = $this->argument('sector');

        if (!$all && !$sectorInput) {
            $this->error('Please specify a sector or use --all flag');
            return 1;
        }

        $sectors = [];

        if ($all) {
            $sectors = Sector::all();
            $this->info("Creating tables for all " . $sectors->count() . " sectors...");
        } else {
            // Try to find by ID first, then by name
            $sector = is_numeric($sectorInput) 
                ? Sector::find($sectorInput) 
                : Sector::where('name', $sectorInput)->first();

            if (!$sector) {
                $this->error("Sector not found: {$sectorInput}");
                return 1;
            }

            $sectors = collect([$sector]);
            $this->info("Creating tables for sector: {$sector->name} (ID: {$sector->id})");
        }

        $created = 0;
        $skipped = 0;

        foreach ($sectors as $sector) {
            $result = $this->createTablesForSector($sector, $force);
            
            if ($result['weight_transactions']) {
                $created++;
                $this->line("  ✓ Created weight_transactions table for: {$sector->name}");
            } else {
                $skipped++;
                $this->line("  - Skipped weight_transactions table for: {$sector->name} (already exists)");
            }

            if ($result['transaction_images']) {
                $created++;
                $this->line("  ✓ Created transaction_images table for: {$sector->name}");
            } else {
                $skipped++;
                $this->line("  - Skipped transaction_images table for: {$sector->name} (already exists)");
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Created: {$created} tables");
        $this->info("  Skipped: {$skipped} tables");

        return 0;
    }

    /**
     * Create sector-specific tables for a given sector.
     *
     * @param Sector $sector
     * @param bool $force
     * @return array
     */
    private function createTablesForSector(Sector $sector, bool $force): array
    {
        $sanitizedName = $this->sanitizeSectorName($sector->name);
        $tableSuffix = Str::snake($sanitizedName);

        $result = [
            'weight_transactions' => false,
            'transaction_images' => false,
        ];

        // Create weight_transactions_<sector> table
        $wtTable = 'weight_transactions_' . $tableSuffix;
        if (!Schema::hasTable($wtTable) || $force) {
            if ($force && Schema::hasTable($wtTable)) {
                Schema::dropIfExists($wtTable);
            }
            
            $this->createWeightTransactionsTable($wtTable);
            $result['weight_transactions'] = true;
        }

        // Create transaction_images_<sector> table
        $imgTable = 'transaction_images_' . $tableSuffix;
        if (!Schema::hasTable($imgTable) || $force) {
            if ($force && Schema::hasTable($imgTable)) {
                Schema::dropIfExists($imgTable);
            }
            
            $this->createTransactionImagesTable($imgTable);
            $result['transaction_images'] = true;
        }

        return $result;
    }

    /**
     * Create a weight_transactions table with given name.
     *
     * @param string $tableName
     * @return void
     */
    private function createWeightTransactionsTable(string $tableName): void
    {
        Schema::create($tableName, function ($table) {
            $table->id();
            $table->string('transaction_id', 100)->nullable()->index();
            $table->string('weight_type', 50)->nullable();
            $table->string('transfer_type', 50)->nullable();
            $table->string('select_mode', 50)->nullable();
            $table->string('vehicle_type', 50)->nullable();
            $table->string('vehicle_no', 50)->nullable()->index();
            $table->string('material', 100)->nullable();
            $table->string('productType', 100)->nullable();
            
            $table->decimal('gross_weight', 10, 2)->nullable();
            $table->timestamp('gross_time')->nullable();
            $table->string('gross_operator', 100)->nullable();
            
            $table->decimal('tare_weight', 10, 2)->nullable();
            $table->timestamp('tare_time')->nullable();
            $table->string('tare_operator', 100)->nullable();
            
            $table->decimal('volume', 10, 2)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('deduction', 10, 2)->nullable();
            $table->decimal('real_net', 10, 2)->nullable();
            
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->string('customer_name', 150)->nullable();
            $table->string('vendor_name', 150)->nullable();
            $table->string('sale_id', 100)->nullable();
            $table->string('purchase_id', 100)->nullable();
            
            $table->unsignedBigInteger('sector_id')->nullable()->index();
            $table->string('sector_name', 150)->nullable();
            
            $table->text('note')->nullable();
            $table->text('others')->nullable();
            $table->string('username', 100)->nullable();
            $table->string('status', 50)->nullable()->default('Unfinished');
            $table->string('detection', 50)->nullable();
            $table->boolean('isSynced')->default(false)->index();
            
            $table->timestamps();
        });
    }

    /**
     * Create a transaction_images table with given name.
     *
     * @param string $tableName
     * @return void
     */
    private function createTransactionImagesTable(string $tableName): void
    {
        Schema::create($tableName, function ($table) {
            $table->id();
            $table->unsignedBigInteger('weighing_id')->nullable()->index();
            $table->string('transaction_id', 128)->nullable()->index();
            $table->unsignedBigInteger('sector_id')->nullable()->index();
            $table->string('mode', 20)->nullable()->index();
            $table->string('camera_no', 16)->nullable();
            $table->timestamp('captured_at')->nullable()->index();
            $table->string('image_path', 512)->nullable();
            $table->string('storage_backend', 32)->nullable()->default('local');
            $table->string('content_type', 32)->nullable();
            $table->unsignedInteger('size_bytes')->nullable();
            $table->char('checksum_sha256', 64)->nullable()->index();
            $table->string('ingest_status', 32)->nullable()->default('pending');
            $table->json('extra_meta')->nullable();
            $table->boolean('isSynced')->default(false)->index();
            $table->timestamps();
            
            // Composite index for deduplication
            $table->index(['weighing_id', 'checksum_sha256']);
            $table->index(['transaction_id', 'checksum_sha256']);
        });
    }

    /**
     * Sanitize sector name for use in table names.
     *
     * @param string $name
     * @return string
     */
    private function sanitizeSectorName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_\s]/', '', $name);
    }
}
