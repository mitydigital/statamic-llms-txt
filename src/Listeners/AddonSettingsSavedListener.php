<?php

namespace MityDigital\StatamicLlmsTxt\Listeners;

use MityDigital\StatamicLlmsTxt\Actions\ClearCache;
use Statamic\Events\AddonSettingsSaved;

class AddonSettingsSavedListener
{
    public function handle(AddonSettingsSaved $event): void
    {
        if ($event->settings->addon()->id() === 'mitydigital/statamic-llms-txt') {
            app(ClearCache::class)();
        }
    }
}
