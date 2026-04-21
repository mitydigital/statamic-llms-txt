<?php

namespace MityDigital\StatamicLlmsTxt;

use MityDigital\StatamicLlmsTxt\Facades\StatamicLlmsTxt;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        $this->registerSettingsBlueprint(StatamicLlmsTxt::blueprint());

        $this->app->bind('StatamicLlmsTxt', function () {
            return new Support\StatamicLlmsTxt;
        });
    }
}
