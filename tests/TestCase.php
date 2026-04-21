<?php

namespace MityDigital\StatamicLlmsTxt\Tests;

use Illuminate\Support\Facades\File;
use MityDigital\StatamicLlmsTxt\ServiceProvider;
use Statamic\Statamic;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        Statamic::booted(fn () => $app['config']->set('statamic.editions.pro', true));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(base_path('content'));

        parent::tearDown();
    }
}
