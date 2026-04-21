<?php

namespace MityDigital\StatamicLlmsTxt\Listeners;

use MityDigital\StatamicLlmsTxt\Actions\ClearCache;
use Statamic\Events\EntrySaved;

class StatamicContentChangedListener
{
    public function handle(EntrySaved $event): void
    {
        app(ClearCache::class)();
    }
}
