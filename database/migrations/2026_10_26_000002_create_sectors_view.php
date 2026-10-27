<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * (Re)creates the `sectors_view` database view.
 *
 * The repo never had a migration for this view even though the web sector
 * filter (`WeightTransactionController::filterOptions`) and
 * `GET /api/sector-view-all` (consumed by the desktop scale app) read it, so the
 * definition lives here now. The column list is the one the app depends on today
 * plus `sectorCode`; `id` and `name` keep their names for backward compatibility.
 *
 * `CREATE OR REPLACE VIEW` can neither rename, reorder nor retype existing
 * columns, and the live definition is not part of this repo. Therefore the
 * existing definition is inspected first: an exact column match is replaced in
 * place (idempotent re-run), anything else is dropped and rebuilt with the
 * canonical shape below.
 */
return new class extends Migration
{
    private const VIEW = 'sectors_view';

    /**
     * table, alias, source column, view column, type used when the source
     * column does not exist on the current database.
     *
     * @var array<int, array{0:string,1:string,2:string,3:string,4:string}>
     */
    private const SELECT_MAP = [
        ['sectors', 's', 'id', 'id', 'bigint'],
        ['sectors', 's', 'companyId', 'companyId', 'bigint'],
        ['companies', 'c', 'nameEn', 'companyName', 'varchar(255)'],
        ['sectors', 's', 'name', 'name', 'varchar(255)'],
        ['sectors', 's', 'isFarm', 'isFarm', 'boolean'],
        ['sectors', 's', 'isSalesPoint', 'isSalesPoint', 'boolean'],
        ['sectors', 's', 'salesPointName', 'salesPointName', 'varchar(255)'],
        ['sectors', 's', 'feedDepotCost', 'feedDepotCost', 'numeric'],
        ['sectors', 's', 'chicksDepotCost', 'chicksDepotCost', 'numeric'],
        ['sectors', 's', 'sectorType', 'sectorType', 'varchar(255)'],
        ['sectors', 's', 'inchargeName', 'inchargeName', 'varchar(255)'],
        ['sectors', 's', 'inchargePhone', 'inchargePhone', 'varchar(255)'],
        ['sectors', 's', 'inchargeAddress', 'inchargeAddress', 'varchar(255)'],
        ['sectors', 's', 'description', 'description', 'text'],
        ['sectors', 's', 'status', 'status', 'varchar(255)'],
        ['sectors', 's', 'created_at', 'created_at', 'timestamp'],
        ['sectors', 's', 'updated_at', 'updated_at', 'timestamp'],
        ['sectors', 's', 'sectorCode', 'sectorCode', 'bigint'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('sectors')) {
            return;
        }

        $existing = $this->existingColumns();
        $canReplace = $existing === $this->expectedColumns();

        if (! $canReplace && $existing !== []) {
            DB::statement('DROP VIEW IF EXISTS '.$this->quote(self::VIEW));
        }

        DB::statement($this->viewSql($canReplace));
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS '.$this->quote(self::VIEW));
    }

    /**
     * @return array<int, string>
     */
    private function expectedColumns(): array
    {
        return array_map(static fn (array $map): string => $map[3], self::SELECT_MAP);
    }

    /**
     * Current column names of the view, in definition order.
     *
     * @return array<int, string>
     */
    private function existingColumns(): array
    {
        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'pgsql') {
                $rows = DB::select(
                    "SELECT a.attname AS column_name
                       FROM pg_attribute a
                       JOIN pg_class c ON c.oid = a.attrelid
                       JOIN pg_namespace n ON n.oid = c.relnamespace
                      WHERE c.relname = ?
                        AND c.relkind = 'v'
                        AND n.nspname = current_schema()
                        AND a.attnum > 0
                        AND NOT a.attisdropped
                      ORDER BY a.attnum",
                    [self::VIEW]
                );
            } elseif ($driver === 'sqlite') {
                $rows = DB::select('PRAGMA table_info('.$this->quote(self::VIEW).')');
            } else {
                $rows = DB::select(
                    'SELECT COLUMN_NAME AS column_name FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
                      ORDER BY ORDINAL_POSITION',
                    [self::VIEW]
                );
            }
        } catch (\Throwable $e) {
            return [];
        }

        return array_values(array_map(
            static fn ($row): string => (string) (((array) $row)['column_name'] ?? ((array) $row)['name'] ?? ''),
            $rows
        ));
    }

    private function viewSql(bool $orReplace): string
    {
        $hasCompanyJoin = Schema::hasTable('companies')
            && Schema::hasColumn('companies', 'id')
            && Schema::hasColumn('sectors', 'companyId');

        $selects = [];

        foreach (self::SELECT_MAP as [$table, $alias, $column, $output, $nullType]) {
            $available = $table === 'companies'
                ? ($hasCompanyJoin && Schema::hasColumn('companies', $column))
                : Schema::hasColumn('sectors', $column);

            $expression = $available
                ? $alias.'.'.$this->quote($column)
                : $this->nullExpression($nullType);

            $selects[] = $expression.' AS '.$this->quote($output);
        }

        $sql = 'CREATE '.($orReplace ? 'OR REPLACE ' : '').'VIEW '.$this->quote(self::VIEW)." AS\n"
            ."    SELECT\n        ".implode(",\n        ", $selects)."\n"
            .'    FROM '.$this->quote('sectors').' s';

        if ($hasCompanyJoin) {
            $sql .= "\n".'    LEFT JOIN '.$this->quote('companies').' c ON c.'.$this->quote('id')
                .' = s.'.$this->quote('companyId');
        }

        if (Schema::hasColumn('sectors', 'deleted_at')) {
            $sql .= "\n".'    WHERE s.'.$this->quote('deleted_at').' IS NULL';
        }

        return $sql;
    }

    private function nullExpression(string $type): string
    {
        // PostgreSQL cannot store an untyped NULL in a view.
        return DB::connection()->getDriverName() === 'pgsql' ? 'NULL::'.$type : 'NULL';
    }

    private function quote(string $identifier): string
    {
        return DB::connection()->getDriverName() === 'mysql' || DB::connection()->getDriverName() === 'mariadb'
            ? '`'.str_replace('`', '``', $identifier).'`'
            : '"'.str_replace('"', '""', $identifier).'"';
    }
};
