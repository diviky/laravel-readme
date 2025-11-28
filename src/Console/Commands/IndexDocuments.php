<?php

declare(strict_types=1);

namespace Diviky\Readme\Console\Commands;

use Diviky\Readme\Services\DocumentIndexingService;
use Illuminate\Console\Command;

class IndexDocuments extends Command
{
    protected $signature = 'readme:index {--type= : Index specific version only} {--force : Re-index even if already indexed}';

    protected $description = 'Index all documentation files for search';

    public function handle(DocumentIndexingService $service): int
    {
        $version = $this->option('type');
        $force = $this->option('force');

        $this->info('Starting documentation indexing...');

        if ($version) {
            $this->info("Indexing version: {$version}");
        } else {
            $this->info('Indexing all versions...');
        }

        $count = $service->indexAllDocuments($version, $force);

        $this->info("Successfully indexed {$count} documents.");

        return Command::SUCCESS;
    }
}
