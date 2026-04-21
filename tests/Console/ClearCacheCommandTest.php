<?php

use Illuminate\Support\Facades\Cache;
use MityDigital\StatamicLlmsTxt\Console\Commands\ClearCacheCommand;

it('clears the cache using defaults', function () {
    Cache::shouldReceive('forget')
        ->once()
        ->with('mitydigital-statamic-llms-txt');

    $command = mock(ClearCacheCommand::class)->makePartial();

    $command->shouldReceive('info')
        ->once()
        ->with(__('statamic-llms-txt::console.clear-cache.success'));

    $command->handle();
});

it('shows a warning when caching is disabled (but still clears the cache)', function () {
    config()->set('statamic-llms-txt.cache.enabled', false);
    config()->set('statamic-llms-txt.cache.key', 'llms-test-key');

    Cache::shouldReceive('forget')
        ->once()
        ->with('llms-test-key');

    $command = mock(ClearCacheCommand::class)->makePartial();

    $command->shouldReceive('info')
        ->once()
        ->with(__('statamic-llms-txt::console.clear-cache.success'));

    $command->shouldReceive('warn')
        ->once()
        ->with(__('statamic-llms-txt::console.clear-cache.disabled'));

    $command->handle();
});
