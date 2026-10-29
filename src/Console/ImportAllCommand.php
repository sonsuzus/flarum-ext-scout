<?php

namespace ClarkWinkelmann\Scout\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;

class ImportAllCommand extends Command
{
    use ModifiedImportTrait;

    protected $signature = 'scout:import-all
            {--c|chunk= : The number of records to import at a time (Defaults to configuration value: `scout.chunk.searchable`)}';

    protected $description = 'Import all Flarum models into the search index';

    public function handle(Dispatcher $events): int
    {
        $models = array_keys(resolve('scout.attributes'));

        foreach ($models as $class) {
            $this->importModel($class, $events);
        }

        return self::SUCCESS;
    }
}