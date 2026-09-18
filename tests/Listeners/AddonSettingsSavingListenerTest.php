<?php

use MityDigital\StatamicLlmsTxt\Listeners\AddonSettingsSavingListener;
use Statamic\Events\AddonSettingsSaving;

it('updates the llms addon content before it is saved', function () {
    $settings = mockSettings('mitydigital/statamic-llms-txt', [[
        'type' => 'paragraph',
        'content' => [],
    ]]);
    $settings->shouldReceive('set')->once()->with('content', [[
        'type' => 'paragraph',
        'content' => [],
    ]]);

    app(AddonSettingsSavingListener::class)->handle(
        new AddonSettingsSaving($settings),
    );
});

it('does not update settings belonging to another addon', function () {
    $settings = mockSettings('vendor/another-addon', []);
    $settings->shouldNotReceive('get');
    $settings->shouldNotReceive('set');

    app(AddonSettingsSavingListener::class)->handle(
        new AddonSettingsSaving($settings),
    );
});

function mockSettings(string $addonId, mixed $content): object
{
    $addon = mock();
    $addon->shouldReceive('id')->andReturn($addonId);

    $settings = mock();
    $settings->shouldReceive('addon')->andReturn($addon);
    $settings->shouldReceive('get')->with('content')->andReturn($content);

    return $settings;
}
