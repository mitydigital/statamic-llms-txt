<?php

use Illuminate\Support\Facades\Event;
use MityDigital\StatamicLlmsTxt\Actions\ClearCache;
use MityDigital\StatamicLlmsTxt\Listeners\StatamicContentChangedListener;
use Statamic\Events\AddonSettingsSaved;
use Statamic\Events\EntrySaved;

it('is registered to listen for entry saved events', function () {
    Event::fake();

    Event::assertListening(EntrySaved::class, StatamicContentChangedListener::class);
});

it('is not registered to listen for addon settings saved events', function () {
    Event::fake();

    $dispatcherListeners = app('events')->getListeners(AddonSettingsSaved::class);

    $hasListener = collect($dispatcherListeners)->contains(function ($listenerClosure) {
        $listener = (new ReflectionFunction($listenerClosure))->getStaticVariables()['listener'] ?? null;

        if (is_string($listener)) {
            return str_starts_with($listener, StatamicContentChangedListener::class.'@')
                || $listener === StatamicContentChangedListener::class;
        }

        if (is_array($listener)) {
            return ($listener[0] ?? null) === StatamicContentChangedListener::class;
        }

        return false;
    });

    expect($hasListener)->toBeFalse();
});

it('clears cache when handling an entry saved event', function () {
    $clearCache = mock(ClearCache::class);
    $clearCache->shouldReceive('__invoke')->once();
    $this->app->instance(ClearCache::class, $clearCache);

    $event = mock(EntrySaved::class);

    app(StatamicContentChangedListener::class)->handle($event);
});
