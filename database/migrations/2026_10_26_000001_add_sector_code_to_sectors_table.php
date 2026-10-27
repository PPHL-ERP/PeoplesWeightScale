<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the canonical, HRM-managed sector identity columns to `sectors`.
 *
 * The live PostgreSQL schema is ahead of this migration chain (feedDepotCost,
 * chicksDepotCost, sectorType and the incharge columns were added out of band),
 * so every change here is defensive: nothing is added twice and no local id is
 * ever used as a fallback for `sectorCode`. Existing rows stay NULL until the
 * first HRM sync adopts them by name.
 */
return new class extends Migration
{
    private const TABLE = 'sectors';

    private const UNIQUE_INDEX = 'sectors_sectorcode_unique';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        $hasSectorCode = Schema::hasColumn(self::TABLE, 'sectorCode');
        $hasSyncedAt = Schema::hasColumn(self::TABLE, 'synced_at');
        $hasSource = Schema::hasColumn(self::TABLE, 'source');

        if ($hasSectorCode && $hasSyncedAt && $hasSource) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) use ($hasSectorCode, $hasSyncedAt, $hasSource) {
            if (! $hasSectorCode) {
                // System-managed: assigned by HRM, unique, never derived from the row id.
                $table->unsignedBigInteger('sectorCode')->nullable()->unique();
            }

            if (! $hasSyncedAt) {
                $table->timestamp('synced_at')->nullable();
            }

            if (! $hasSource) {
                $table->string('source', 32)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            return;
        }

        if (Schema::hasColumn(self::TABLE, 'sectorCode') && $this->uniqueIndexExists()) {
            Schema::table(self::TABLE, function (Blueprint $table) {
                $table->dropUnique(self::UNIQUE_INDEX);
            });
        }

        $drop = array_values(array_filter([
            Schema::hasColumn(self::TABLE, 'sectorCode') ? 'sectorCode' : null,
            Schema::hasColumn(self::TABLE, 'synced_at') ? 'synced_at' : null,
            Schema::hasColumn(self::TABLE, 'source') ? 'source' : null,
        ]));

        if ($drop === []) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) use ($drop) {
            $table->dropColumn($drop);
        });
    }

    /**
     * The index may be missing on databases where `sectorCode` was added out of band.
     */
    private function uniqueIndexExists(): bool
    {
        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'pgsql') {
                return DB::selectOne(
                    'SELECT 1 FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ? AND indexname = ?',
                    [self::TABLE, self::UNIQUE_INDEX]
                ) !== null;
            }

            if ($driver === 'mysql' || $driver === 'mariadb') {
                return DB::selectOne(
                    'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
                    [self::TABLE, self::UNIQUE_INDEX]
                ) !== null;
            }

            if ($driver === 'sqlite') {
                return DB::selectOne(
                    "SELECT 1 FROM sqlite_master WHERE type = 'index' AND tbl_name = ? AND name = ?",
                    [self::TABLE, self::UNIQUE_INDEX]
                ) !== null;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }
};
