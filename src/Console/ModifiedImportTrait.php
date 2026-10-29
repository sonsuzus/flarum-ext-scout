<?php

namespace ClarkWinkelmann\Scout\Console;

use ClarkWinkelmann\Scout\ScoutStatic;
use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Scout\Events\ModelsImported;

trait ModifiedImportTrait
{
    protected function importModel(string $class, Dispatcher $events): void
    {
        $events->listen(ModelsImported::class, function (ModelsImported $event) use ($class) {
            $key = $event->models->last()->getScoutKey();

            $this->line('<comment>Imported [' . $class . '] models up to ID:</comment> ' . $key);
        });

        ScoutStatic::makeAllSearchable($class, $this->option('chunk') ? (int)$this->option('chunk') : null);

        $events->forget(ModelsImported::class);

        $this->info('All [' . $class . '] records have been imported.');
    }
}