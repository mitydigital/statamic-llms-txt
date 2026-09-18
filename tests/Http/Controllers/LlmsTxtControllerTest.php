<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use MityDigital\StatamicLlmsTxt\Support\BardToMarkdown;
use Statamic\Facades\Addon;

it('has valid defaults', function () {
    // enabled, key and remember forever
    Cache::shouldReceive('rememberForever')
        ->once()
        ->withArgs(function ($key, $callback) {
            expect($key)->toBe('mitydigital-statamic-llms-txt');
            expect($callback)->toBeCallable();

            return true;
        })
        ->andReturn('cached-forever-markdown-default');

    $response = $this->get('/llms.txt');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    $response->assertSeeText('cached-forever-markdown-default');
});

it('uses rememberForever when caching is enabled and duration is null', function () {
    config()->set('statamic-llms-txt.cache.enabled', true);
    config()->set('statamic-llms-txt.cache.duration', null);
    config()->set('statamic-llms-txt.cache.key', 'llms-test-key');

    Cache::shouldReceive('rememberForever')
        ->once()
        ->withArgs(function ($key, $callback) {
            expect($key)->toBe('llms-test-key');
            expect($callback)->toBeCallable();

            return true;
        })
        ->andReturn('cached-forever-markdown');

    $response = $this->get('/llms.txt');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    $response->assertSeeText('cached-forever-markdown');
});

it('uses remember with a ttl when caching is enabled and duration is set', function () {
    config()->set('statamic-llms-txt.cache.enabled', true);
    config()->set('statamic-llms-txt.cache.duration', 5);
    config()->set('statamic-llms-txt.cache.key', 'llms-test-key');

    Cache::shouldReceive('remember')
        ->once()
        ->withArgs(function ($key, $ttl, $callback) {
            expect($key)->toBe('llms-test-key');
            expect($ttl)->toBeInstanceOf(Carbon::class);
            expect($callback)->toBeCallable();

            return true;
        })
        ->andReturn('cached-ttl-markdown');

    $response = $this->get('/llms.txt');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    $response->assertSeeText('cached-ttl-markdown');
});

it('bypasses cache when caching is disabled', function () {
    config()->set('statamic-llms-txt.cache.enabled', false);

    $addonSettings = new class
    {
        public function get($key)
        {
            return [];
        }
    };

    $addon = new class($addonSettings)
    {
        public function __construct(private $settings) {}

        public function settings()
        {
            return $this->settings;
        }
    };

    Addon::shouldReceive('get')
        ->once()
        ->with('mitydigital/statamic-llms-txt')
        ->andReturn($addon);

    $converter = mock(BardToMarkdown::class);
    $converter->shouldReceive('convert')
        ->once()
        ->with([])
        ->andReturn('generated-without-cache');

    $this->app->instance(BardToMarkdown::class, $converter);

    Cache::spy();

    $response = $this->get('/llms.txt');

    Cache::shouldNotHaveReceived('remember');
    Cache::shouldNotHaveReceived('rememberForever');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    $response->assertSeeText('generated-without-cache');
});
