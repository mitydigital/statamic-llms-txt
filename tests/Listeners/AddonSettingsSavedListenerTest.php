<?php

use MityDigital\StatamicLlmsTxt\Actions\ClearCache;
use MityDigital\StatamicLlmsTxt\Listeners\AddonSettingsSavedListener;
use Statamic\Events\AddonSettingsSaved;

it('runs the clear cache action when the llms addon settings are saved', function () {
    $clearCache = mock(ClearCache::class);
    $clearCache->shouldReceive('__invoke')->once();
    $this->app->instance(ClearCache::class, $clearCache);

    $event = new AddonSettingsSaved(new class
    {
        public function addon()
        {
            return new class
            {
                public function id(): string
                {
                    return 'mitydigital/statamic-llms-txt';
                }
            };
        }
    });

    app(AddonSettingsSavedListener::class)->handle($event);
});

it('does not run the clear cache action for other addon settings', function () {
    $clearCache = mock(ClearCache::class);
    $clearCache->shouldReceive('__invoke')->never();
    $this->app->instance(ClearCache::class, $clearCache);

    $event = new AddonSettingsSaved(new class
    {
        public function addon()
        {
            return new class
            {
                public function id(): string
                {
                    return 'vendor/another-addon';
                }
            };
        }
    });

    app(AddonSettingsSavedListener::class)->handle($event);
});
