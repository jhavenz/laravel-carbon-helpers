<?php

namespace Sourcefli\CarbonHelpers\Commands;

use Illuminate\Console\Command;

class CarbonHelpersCommand extends Command
{
    public $signature = 'laravel-carbon-helpers';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
