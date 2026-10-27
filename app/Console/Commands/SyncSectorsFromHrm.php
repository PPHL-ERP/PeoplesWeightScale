<?php

namespace App\Console\Commands;

use App\Services\HrmSectorSyncService;
use Illuminate\Console\Command;

class SyncSectorsFromHrm extends Command
{
    protected $signature = 'weight-scale:sync-sectors-from-hrm
                            {--dry-run : Report what would change without writing}
                            {--token= : Override the configured HRM API bearer token}';

    protected $description = 'Sync sectors (and their canonical sectorCode) from HRM, the source of truth';

    public function __construct(private readonly HrmSectorSyncService $sectorSync)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $token = $this->option('token');
        if ($token !== null) {
            config(['hrm.token' => (string) $token]);
        }

        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->info('Dry run: no sectors will be written.');
        }

        $stats = $this->sectorSync->sync($dryRun);

        $this->table(
            ['metric', 'count'],
            [
                [$dryRun ? 'would_create' : 'created', $stats['created']],
                [$dryRun ? 'would_update' : 'updated', $stats['updated']],
                ['unchanged', $stats['unchanged']],
                ['skipped', $stats['skipped']],
                ['fetched', $stats['fetched']],
                ['errors', count($stats['errors'])],
            ]
        );

        foreach ($stats['errors'] as $error) {
            $this->error($error);
        }

        return $stats['errors'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
