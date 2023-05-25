<?php
declare(strict_types=1);

namespace Tkachikov\Packages\Providers;

use Illuminate\Support\ServiceProvider;
use Tkachikov\Packages\Console\Commands\PackagesLoadCommand;
use Tkachikov\Packages\Console\Commands\PackagesInfoLoadCommand;

class PackagesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
            $this->commands([
                PackagesLoadCommand::class,
                PackagesInfoLoadCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        //
    }
}
