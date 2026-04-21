<?php

use Illuminate\Support\Facades\Cache;
use MityDigital\StatamicLlmsTxt\Actions\ClearCache;

it('forgets cache using the configured key', function () {
    config()->set('statamic-llms-txt.cache.key', 'llms-custom-key');

    Cache::shouldReceive('forget')
        ->once()
        ->with('llms-custom-key');

    app(ClearCache::class)();
});

it('falls back to the default cache key when config is missing', function () {
    config()->set('statamic-llms-txt.cache', []);

    Cache::shouldReceive('forget')
        ->once()
        ->with('mitydigital-statamic-llms-txt');

    app(ClearCache::class)();
});
