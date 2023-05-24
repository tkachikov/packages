<?php
declare(strict_types=1);

namespace Tkachikov\Packages\Providers;

use Illuminate\Support\ServiceProvider;

class PackagesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        }
    }

    public function register(): void
    {
        //
    }
}
