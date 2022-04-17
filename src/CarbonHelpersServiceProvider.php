<?php

namespace Sourcefli\CarbonHelpers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CarbonHelpersServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-carbon-helpers')
            ->hasConfigFile();
    }
}
