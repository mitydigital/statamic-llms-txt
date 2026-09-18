<?php

use MityDigital\StatamicLlmsTxt\Support\BardLinkResolver;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;

beforeEach(function () {
    config()->set('app.url', 'https://app.test');
    config()->set('statamic-llms-txt.convert_urls_to_entries', [
        'enabled' => true,
        'urls' => [],
    ]);
});

it('converts APP_URL links without configured URLs', function () {
    $site = mockSite();
    $entry = mockEntry('entry-app');

    Site::shouldReceive('findByUrl')->once()->with('https://app.test/about')->andReturn($site);
    Entry::shouldReceive('findByUri')->once()->with('/about', 'default')->andReturn($entry);

    expect(app(BardLinkResolver::class)->resolve(bardContent('https://app.test/about')))
        ->toBe(bardContent('statamic://entry::entry-app'));
});

it('converts configured URLs as well as APP_URL links', function () {
    config()->set('statamic-llms-txt.convert_urls_to_entries.urls', ['https://www.example.com']);

    $site = mockSite();
    $appEntry = mockEntry('entry-app');
    $configuredEntry = mockEntry('entry-configured');

    Site::shouldReceive('findByUrl')->once()->with('https://app.test/about')->andReturn($site);
    Site::shouldReceive('findByUrl')->once()->with('https://www.example.com/contact')->andReturn($site);
    Entry::shouldReceive('findByUri')->once()->with('/about', 'default')->andReturn($appEntry);
    Entry::shouldReceive('findByUri')->once()->with('/contact', 'default')->andReturn($configuredEntry);

    $content = bardContent('https://app.test/about', 'https://www.example.com/contact');

    expect(app(BardLinkResolver::class)->resolve($content))
        ->toBe(bardContent('statamic://entry::entry-app', 'statamic://entry::entry-configured'));
});

it('converts relative URLs and preserves query strings and fragments', function () {
    $site = mockSite();
    $entry = mockEntry('entry-guides');

    Site::shouldReceive('findByUrl')->once()->with('https://app.test/guides?topic=bard#links')->andReturn($site);
    Entry::shouldReceive('findByUri')->once()->with('/guides', 'default')->andReturn($entry);

    expect(app(BardLinkResolver::class)->resolve(bardContent('/guides?topic=bard#links')))
        ->toBe(bardContent('statamic://entry::entry-guides?topic=bard#links'));
});

it('does not rewrite links when conversion is disabled', function () {
    config()->set('statamic-llms-txt.convert_urls_to_entries.enabled', false);

    $content = bardContent('https://app.test/about');

    expect(app(BardLinkResolver::class)->resolve($content))->toBe($content);
});

it('resolves links in nested Bard content', function () {
    $site = mockSite();
    $entry = mockEntry('entry-nested');

    Site::shouldReceive('findByUrl')->once()->with('https://app.test/nested')->andReturn($site);
    Entry::shouldReceive('findByUri')->once()->with('/nested', 'default')->andReturn($entry);

    $content = [[
        'type' => 'bulletList',
        'content' => [[
            'type' => 'listItem',
            'content' => bardContent('/nested'),
        ]],
    ]];

    expect(data_get(app(BardLinkResolver::class)->resolve($content), '0.content.0.content.0.content.0.marks.0.attrs.href'))
        ->toBe('statamic://entry::entry-nested');
});

it('leaves external, existing internal, and non-page links unchanged', function () {
    $content = bardContent(
        'https://external.test/page',
        'statamic://entry::already-internal',
        'mailto:hello@example.com',
    );

    expect(app(BardLinkResolver::class)->resolve($content))->toBe($content);
});

it('leaves a watched URL unchanged when it cannot be resolved to an entry', function () {
    config()->set('statamic-llms-txt.convert_urls_to_entries.urls', ['https://www.example.com']);

    $site = mockSite();

    Site::shouldReceive('findByUrl')->once()->with('https://www.example.com/missing')->andReturn($site);
    Entry::shouldReceive('findByUri')->once()->with('/missing', 'default')->andReturnNull();

    $content = bardContent('https://www.example.com/missing');

    expect(app(BardLinkResolver::class)->resolve($content))->toBe($content);
});

function mockSite(): object
{
    $site = mock();
    $site->shouldReceive('handle')->andReturn('default');
    $site->shouldReceive('absoluteUrl')->andReturn('https://app.test');

    return $site;
}

function mockEntry(string $id): EntryContract
{
    $entry = mock(EntryContract::class);
    $entry->shouldReceive('id')->andReturn($id);

    return $entry;
}

function bardContent(string ...$hrefs): array
{
    return [[
        'type' => 'paragraph',
        'content' => array_map(fn (string $href): array => [
            'type' => 'text',
            'text' => 'Link',
            'marks' => [[
                'type' => 'link',
                'attrs' => ['href' => $href],
            ]],
        ], $hrefs),
    ]];
}
