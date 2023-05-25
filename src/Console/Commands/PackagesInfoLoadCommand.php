<?php
declare(strict_types=1);

namespace Tkachikov\Packages\Console\Commands;

use Illuminate\Console\Command;
use Tkachikov\Packages\Services\LoadService;

class PackagesInfoLoadCommand extends Command
{
    protected $signature = 'packages:load-info';

    protected $description = '';

    public function handle(LoadService $service): int
    {
        $service
            ->output($this->output)
            ->packagesInfoLoad();

        return self::SUCCESS;
    }
}
