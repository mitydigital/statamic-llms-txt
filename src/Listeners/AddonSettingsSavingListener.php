<?php

namespace MityDigital\StatamicLlmsTxt\Listeners;

use MityDigital\StatamicLlmsTxt\Support\BardLinkResolver;
use Statamic\Events\AddonSettingsSaving;

class AddonSettingsSavingListener
{
    public function __construct(private BardLinkResolver $bardLinkResolver) {}

    public function handle(AddonSettingsSaving $event): void
    {
        if ($event->settings->addon()->id() !== 'mitydigital/statamic-llms-txt') {
            return;
        }

        $content = $event->settings->get('content');

        if (! is_array($content)) {
            return;
        }

        $event->settings->set('content', $this->bardLinkResolver->resolve($content));
    }
}
