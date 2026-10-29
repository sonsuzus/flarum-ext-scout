<?php

namespace ClarkWinkelmann\Scout\Console;

use ClarkWinkelmann\Scout\ScoutStatic;
use Laravel\Scout\Console\FlushCommand as BaseFlushCommand;

class FlushCommand extends BaseFlushCommand
{
    public function handle(): int
    {
        $class = ScoutStatic::ValidateModelClass((string)$this->argument('model'));

        ScoutStatic::removeAllFromSearch($class);

        $this->info('All [' . $class . '] records have been flushed.');

        return self::SUCCESS;
    }
}