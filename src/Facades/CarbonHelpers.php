<?php

namespace Sourcefli\CarbonHelpers\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Sourcefli\CarbonHelpers\CarbonHelpers
 */
class CarbonHelpers extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-carbon-helpers';
    }
}
