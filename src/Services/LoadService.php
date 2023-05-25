<?php
declare(strict_types=1);

namespace Tkachikov\Packages\Services;

use Exception;
use Throwable;
use Tkachikov\Packages\Models\Keyword;
use Tkachikov\Packages\Models\Package;
use Tkachikov\Packages\Models\PackageKeyword;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Helper\ProgressBar;

class LoadService
{
    private OutputStyle $output;

    private ProgressBar $bar;

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
     * @return void
     */
    public function packagesLoad(): void
    {
        $url = 'https://packagist.org/packages/list.json';
        $chunks = array_chunk(array_map(function ($package) {
            return array_combine(['vendor', 'name'], explode('/', $package));
        }, $this->getData($url)['packageNames']), 1000);
        $this->createProgressBar(count($chunks));
        foreach ($chunks as $packages) {
            Package::insertOrIgnore($packages);
            $this->advanceProgressBar();
        }
        $this->finishProgressBar();
    }

    /**
     * @return void
     */
    public function packagesInfoLoad(): void
    {
        $url = 'https://repo.packagist.org/p2/';
        $builder = Package::whereNull('info');
        $this->createProgressBar($builder->count(), " | package:%message%\n");
        $builder
            ->each(function ($package) use ($url) {
                $this->setMessageProgressBar($package->fullName);
                $data = $this->getData($url.$package->fullName.'.json');
                $lastVersion = $data['packages'][$package->fullName][0] ?? [];
                if ($lastVersion) {
                    $package->update(['info' => $lastVersion]);
                }
                $this->advanceProgressBar();
            });
        $this->finishProgressBar();
    }

    /**
     * @return void
     */
    public function prepareInfo(): void
    {
        $builder = Package::query()
            ->whereNotNull('info')
            ->whereRaw('json_length(info) > 0')
            ->whereRaw("json_length(info->'$.keywords') > 0");
        $this->createProgressBar($builder->count());
        $builder
            ->each(function ($package) {
                foreach ($package->info['keywords'] as $word) {
                    $slug = str($word)->lower()->kebab()->toString();
                    $keyword = Keyword::whereKeyword($slug)->first();
                    if (!$keyword) {
                        $keyword = Keyword::create(['keyword' => $slug]);
                    }
                    $builder = PackageKeyword::query()
                        ->wherePackageId($package->id)
                        ->whereKeywordId($keyword->id);
                    if (!$builder->exists()) {
                        PackageKeyword::create([
                            'package_id' => $package->id,
                            'keyword_id' => $keyword->id,
                        ]);
                    }
                }
                $this->advanceProgressBar();
            });
        $this->finishProgressBar();
    }

    /**
     * @param string $url
     *
     * @return array
     *
     * @throws Exception
     */
    private function getData(string $url): array
    {
        $try = 3;
        while ($try--) {
            try {
                return json_decode(file_get_contents($url), true);
            } catch (Throwable) {
                //
            }
        }
        throw new Exception('Not connection');
    }

    /**
     * @param int    $count
     * @param string $message
     *
     * @return void
     */
    private function createProgressBar(int $count, string $message = ''): void
    {
        if (isset($this->output)) {
            $this->bar = $this->output->createProgressBar($count);
            $this->bar->setFormat($this->bar::getFormatDefinition($this->bar::FORMAT_DEBUG) . $message);
            $this->bar->start();
        }
    }

    /**
     * @return void
     */
    private function advanceProgressBar(): void
    {
        if (isset($this->bar)) {
            $this->bar->advance();
        }
    }

    /**
     * @return void
     */
    private function finishProgressBar(): void
    {
        if (isset($this->bar)) {
            $this->bar->finish();
        }
    }

    /**
     * @param string $message
     *
     * @return void
     */
    private function setMessageProgressBar(string $message): void
    {
        if (isset($this->bar)) {
            $this->bar->setMessage($message);
        }
    }
}
