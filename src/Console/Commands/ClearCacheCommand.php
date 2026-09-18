<?php

namespace MityDigital\StatamicLlmsTxt\Console\Commands;

use Illuminate\Console\Command;
use MityDigital\StatamicLlmsTxt\Actions\ClearCache;

class ClearCacheCommand extends Command
{
    protected $signature = 'llms-txt:clear-cache';

    protected $description = 'Clears the llms.txt cache';

    public function handle(): void
    {
        app(ClearCache::class)();

        $this->info(__('statamic-llms-txt::console.clear-cache.success'));

        if (! config('statamic-llms-txt.cache.enabled')) {
            $this->warn(__('statamic-llms-txt::console.clear-cache.disabled'));
        }
    }
}
