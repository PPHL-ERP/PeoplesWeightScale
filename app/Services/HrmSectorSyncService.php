<?php

namespace App\Services;

use App\Models\Sector;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Mirrors the HRM sector master into Weight Scale.
 *
 * HRM is the source of truth for the canonical integer `sectorCode`. The sync is
 * one-way (HRM -> Weight Scale) and only writes the columns HRM owns; everything
 * else on the sector row (company, sector type, depot costs, incharges, sales
 * point name, description) is preserved.
 *
 * Shard tables (`weight_transactions_<sector_name>`) are derived from the sector
 * name by App\Services\SectorModelResolver and are never touched here.
 */
class HrmSectorSyncService
{
    private const SOURCE = 'hrm';

    private const APPROVED = 'approved';

    /**
     * Cached sector list payloads built by Api\SectorController.
     *
     * @var array<int, string>
     */
    private const SECTOR_LIST_CACHE_KEYS = [
        'sector_list_all',
        'sector_list_sales_points',
        'sector_list_farms',
        'sector_list_chicks',
    ];

    /**
     * Sync approved HRM sectors into the local `sectors` table.
     *
     * @return array{fetched:int,created:int,updated:int,unchanged:int,skipped:int,errors:array<int,string>}
     */
    public function sync(bool $dryRun = false): array
    {
        $stats = [
            'fetched' => 0,
            'created' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        if (! config('hrm.sync_enabled')) {
            $stats['errors'][] = 'HRM sector sync is disabled (HRM_SECTOR_SYNC_ENABLED=false).';

            return $stats;
        }

        try {
            $rows = $this->fetchRows();
        } catch (Throwable $e) {
            $stats['errors'][] = $e->getMessage();

            return $stats;
        }

        $stats['fetched'] = count($rows);

        if ($rows === []) {
            return $stats;
        }

        if ($dryRun) {
            return $this->syncRows($rows, true, $stats);
        }

        try {
            $result = DB::transaction(fn (): array => $this->syncRows($rows, false, $stats));
        } catch (Throwable $e) {
            $stats['errors'][] = 'HRM sector sync aborted: '.$e->getMessage();

            Log::error('HRM sector sync aborted', ['error' => $e->getMessage()]);

            return $stats;
        }

        // Freshly synced codes must not stay hidden behind the cached list payloads.
        if ($result['created'] + $result['updated'] > 0) {
            foreach (self::SECTOR_LIST_CACHE_KEYS as $key) {
                Cache::forget($key);
            }
        }

        return $result;
    }

    /**
     * @param  array<int, mixed>  $rows
     * @param  array{fetched:int,created:int,updated:int,unchanged:int,skipped:int,errors:array<int,string>}  $stats
     * @return array{fetched:int,created:int,updated:int,unchanged:int,skipped:int,errors:array<int,string>}
     */
    private function syncRows(array $rows, bool $dryRun, array $stats): array
    {
        foreach ($rows as $row) {
            if (! is_array($row)) {
                $stats['skipped']++;

                continue;
            }

            // Only the HRM-owned code is trusted here: the local row id must never
            // be used as a sector code.
            $sectorCode = (int) ($row['sectorCode'] ?? $row['sector_code'] ?? 0);
            if ($sectorCode <= 0) {
                $stats['skipped']++;

                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                $stats['skipped']++;

                continue;
            }

            try {
                // Each row runs in its own savepoint so one bad row cannot abort the batch.
                $action = $dryRun
                    ? $this->syncRow($row, $sectorCode, $name, true)
                    : DB::transaction(fn (): string => $this->syncRow($row, $sectorCode, $name, false));

                $stats[$action]++;
            } catch (Throwable $e) {
                $stats['errors'][] = "sectorCode {$sectorCode}: {$e->getMessage()}";

                Log::warning('HRM sector row sync failed', [
                    'sectorCode' => $sectorCode,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return 'created'|'updated'|'unchanged'
     */
    private function syncRow(array $row, int $sectorCode, string $name, bool $dryRun): string
    {
        $isFarm = $this->optionalBool($row, 'isFarm', 'is_farm');
        $isSalesPoint = $this->optionalBool($row, 'isSalesPoint', 'is_sales_point');

        /** @var Sector|null $sector */
        $sector = Sector::withTrashed()->where('sectorCode', $sectorCode)->first();

        if ($sector === null) {
            // First sync: adopt a pre-existing local sector by name so its
            // Weight Scale-only columns survive instead of duplicating the row.
            $sector = Sector::withTrashed()
                ->whereNull('sectorCode')
                ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
                ->first();
        }

        if ($sector === null) {
            if (! $dryRun) {
                Sector::create([
                    'sectorCode' => $sectorCode,
                    'name' => $name,
                    'isFarm' => $isFarm ?? false,
                    'isSalesPoint' => $isSalesPoint ?? false,
                    'status' => self::APPROVED,
                    'source' => self::SOURCE,
                    'synced_at' => now(),
                ]);
            }

            return 'created';
        }

        $restored = $sector->trashed();
        $unchanged = ! $restored
            && (int) $sector->sectorCode === $sectorCode
            && $this->sharedFieldsMatch($sector, $name, $isFarm, $isSalesPoint);

        if ($dryRun) {
            return $unchanged ? 'unchanged' : 'updated';
        }

        if ($restored) {
            $sector->restore();
        }

        if ($sector->name !== $name) {
            // Sector shard tables are derived from the sector name; a rename keeps
            // the old table name, which is intentional.
            Log::warning('HRM sector rename applied', [
                'sectorCode' => $sectorCode,
                'from' => $sector->name,
                'to' => $name,
            ]);
        }

        $shared = [
            'sectorCode' => $sectorCode,
            'name' => $name,
            'status' => self::APPROVED,
            'source' => self::SOURCE,
            'synced_at' => now(),
        ];

        if ($isFarm !== null) {
            $shared['isFarm'] = $isFarm;
        }

        if ($isSalesPoint !== null) {
            $shared['isSalesPoint'] = $isSalesPoint;
        }

        $sector->fill($shared);
        $sector->save();

        return $unchanged ? 'unchanged' : 'updated';
    }

    private function sharedFieldsMatch(Sector $sector, string $name, ?bool $isFarm, ?bool $isSalesPoint): bool
    {
        return $sector->name === $name
            && ($isFarm === null || (bool) $sector->isFarm === $isFarm)
            && ($isSalesPoint === null || (bool) $sector->isSalesPoint === $isSalesPoint)
            && $sector->status === self::APPROVED;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function optionalBool(array $row, string $camel, string $snake): ?bool
    {
        if (array_key_exists($camel, $row)) {
            return (bool) $row[$camel];
        }

        if (array_key_exists($snake, $row)) {
            return (bool) $row[$snake];
        }

        return null;
    }

    /**
     * @return array<int, mixed>
     */
    private function fetchRows(): array
    {
        $failures = [];

        foreach (['/api/v1/getsectorlisturl', '/api/v1/allsectors'] as $path) {
            try {
                return $this->fetch($path);
            } catch (Throwable $e) {
                $failures[] = $path.': '.$e->getMessage();

                Log::warning('HRM sector fetch failed', [
                    'path' => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        throw new RuntimeException('HRM sector endpoints unavailable ('.implode('; ', $failures).').');
    }

    /**
     * @return array<int, mixed>
     */
    private function fetch(string $path): array
    {
        $url = rtrim((string) config('hrm.base_url'), '/').$path;

        $request = Http::timeout((int) config('hrm.timeout', 60))->acceptJson();

        $token = trim((string) config('hrm.token', ''));
        if ($token !== '') {
            $request = $request->withToken($token);
        }

        $response = $request->get($url);

        if (! $response->successful()) {
            throw new RuntimeException('HTTP '.$response->status());
        }

        $payload = $response->json();

        if (is_array($payload) && array_key_exists('data', $payload)) {
            $payload = $payload['data'];
        }

        if (! is_array($payload)) {
            throw new RuntimeException('unexpected payload');
        }

        return $payload;
    }
}
