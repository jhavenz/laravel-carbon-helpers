<?php

namespace Sourcefli\CarbonHelpers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Sourcefli\CarbonHelpers\Commands\CarbonHelpersCommand;

class CarbonHelpersServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-carbon-helpers')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-carbon-helpers_table')
            ->hasCommand(CarbonHelpersCommand::class);
    }
}
