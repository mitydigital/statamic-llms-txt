<?php

namespace MityDigital\StatamicLlmsTxt\Actions;

use Illuminate\Support\Facades\Cache;

class ClearCache
{
    public function __invoke(): void
    {
        Cache::forget(config('statamic-llms-txt.cache.key', 'mitydigital-statamic-llms-txt'));
    }
}
