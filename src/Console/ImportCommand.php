<?php

namespace ClarkWinkelmann\Scout\Console;

use ClarkWinkelmann\Scout\ScoutStatic;
use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Scout\Console\ImportCommand as BaseImportCommand;

class ImportCommand extends BaseImportCommand
{
    use ModifiedImportTrait;

    public function handle(Dispatcher $events): int
    {
        $class = ScoutStatic::ValidateModelClass((string)$this->argument('model'));

        $this->importModel($class, $events);

        return self::SUCCESS;
    }
}