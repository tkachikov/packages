<?php
declare(strict_types=1);

namespace Tkachikov\Packages;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\OutputStyle;
use Tkachikov\Packages\Models\Package;

class Loader
{
    private OutputStyle $output;

    private ProgressBar $bar;

    private readonly array $names;

    /**
     * @param $output
     *
     * @return $this
     */
    public function output($output): self
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @param ProgressBar $bar
     *
     * @return $this
     */
    public function progressBar(ProgressBar $bar): self
    {
        $this->bar = $bar;

        return $this;
    }

    /**
     * @return void
     */
    public function loadPackages(): void
    {
        $url = 'https://packagist.org/packages/list.json';
        $chunks = array_chunk(array_map(function ($package) {
            return array_combine(['vendor', 'name'], explode('/', $package));
        }, $this->getData($url)['packageNames']), 1000);

        $this->bar = $this->output->createProgressBar(count($chunks));
        $this->bar->setFormat($this->bar::getFormatDefinition($this->bar::FORMAT_DEBUG));
        $this->bar->start();

        foreach ($chunks as $packages) {
            Package::insertOrIgnore($packages);
            $this->bar->advance();
        }
        $this->bar->finish();
    }

    /**
     * @return void
     */
    public function loadPackageInfo(): void
    {
        $url = 'https://repo.packagist.org/p2/';
        $builder = Package::whereNull('info');
        $this->bar = $this->output->createProgressBar($builder->count());
        $format = $this->bar::FORMAT_DEBUG . " | package:%message%\n";
        $this->bar->setFormat($this->bar::getFormatDefinition($format));
        $this->bar->start();
        $builder
            ->each(function ($package) use ($url) {
                $this->bar->setMessage($package->fullName);
                $data = $this->getData($url.$package->fullName.'.json');
                $lastVersion = $data['packages'][$package->fullName][0] ?? [];
                if ($lastVersion) {
                    $package->update(['info' => $lastVersion]);
                }
                $this->bar->advance();
            });
        $this->bar->finish();
    }

    /**
     * @param string $url
     *
     * @return array
     */
    private function getData(string $url): array
    {
        return json_decode(file_get_contents($url), true);
    }
}
