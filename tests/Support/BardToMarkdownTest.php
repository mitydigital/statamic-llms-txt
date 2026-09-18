<?php

use MityDigital\StatamicLlmsTxt\Support\BardToMarkdown;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;

it('returns an empty string for empty bard content', function () {
    $converter = app(BardToMarkdown::class);

    expect($converter->convert([]))->toBe('');
});

it('renders supported top-level blocks and skips unknown blocks', function () {
    $converter = app(BardToMarkdown::class);

    $nodes = [
        [
            'type' => 'heading',
            'attrs' => ['level' => 2],
            'content' => [
                ['type' => 'text', 'text' => 'Overview'],
            ],
        ],
        [
            'type' => 'paragraph',
            'content' => [
                ['type' => 'text', 'text' => 'Body text'],
            ],
        ],
        ['type' => 'unsupportedNode'],
        ['type' => 'horizontalRule'],
    ];

    $markdown = $converter->convert($nodes);

    expect($markdown)->toBe("## Overview\n\nBody text\n\n---");
});

it('clamps heading levels and defaults to h2 when level is missing', function () {
    $converter = app(BardToMarkdown::class);

    $nodes = [
        [
            'type' => 'heading',
            'attrs' => ['level' => 0],
            'content' => [['type' => 'text', 'text' => 'Too low']],
        ],
        [
            'type' => 'heading',
            'attrs' => ['level' => 9],
            'content' => [['type' => 'text', 'text' => 'Too high']],
        ],
        [
            'type' => 'heading',
            'content' => [['type' => 'text', 'text' => 'Default']],
        ],
    ];

    $markdown = $converter->convert($nodes);

    expect($markdown)->toBe("# Too low\n\n###### Too high\n\n## Default");
});

it('renders inline marks and hard breaks in paragraphs', function () {
    $converter = app(BardToMarkdown::class);

    $nodes = [[
        'type' => 'paragraph',
        'content' => [
            ['type' => 'text', 'text' => 'Code', 'marks' => [['type' => 'code']]],
            ['type' => 'text', 'text' => ' '],
            ['type' => 'text', 'text' => 'Bold', 'marks' => [['type' => 'bold']]],
            ['type' => 'text', 'text' => ' '],
            ['type' => 'text', 'text' => 'Italic', 'marks' => [['type' => 'italic']]],
            ['type' => 'text', 'text' => ' '],
            ['type' => 'text', 'text' => 'Underline', 'marks' => [['type' => 'underline']]],
            ['type' => 'text', 'text' => ' '],
            ['type' => 'text', 'text' => 'Strike', 'marks' => [['type' => 'strikethrough']]],
            ['type' => 'hardBreak'],
            ['type' => 'text', 'text' => 'Next line'],
        ],
    ]];

    $markdown = $converter->convert($nodes);

    expect($markdown)->toBe("`Code` **Bold** *Italic* <u>Underline</u> ~~Strike~~\nNext line");
});

it('applies multiple marks in provided order', function () {
    $converter = app(BardToMarkdown::class);

    $nodes = [[
        'type' => 'paragraph',
        'content' => [
            [
                'type' => 'text',
                'text' => 'Combo',
                'marks' => [
                    ['type' => 'bold'],
                    ['type' => 'italic'],
                ],
            ],
        ],
    ]];

    expect($converter->convert($nodes))->toBe('***Combo***');
});

it('renders normal links and resolves statamic entry links', function () {
    $converter = app(BardToMarkdown::class);

    $entry = mock(EntryContract::class);
    $entry->shouldReceive('absoluteUrl')->once()->andReturn('https://example.com/resolved');

    Entry::shouldReceive('find')->once()->with('entry-123')->andReturn($entry);

    $nodes = [[
        'type' => 'paragraph',
        'content' => [
            [
                'type' => 'text',
                'text' => 'External',
                'marks' => [[
                    'type' => 'link',
                    'attrs' => ['href' => 'https://example.com/external'],
                ]],
            ],
            ['type' => 'text', 'text' => ' and '],
            [
                'type' => 'text',
                'text' => 'Internal',
                'marks' => [[
                    'type' => 'link',
                    'attrs' => ['href' => 'statamic://entry::entry-123'],
                ]],
            ],
        ],
    ]];

    expect($converter->convert($nodes))
        ->toBe('[External](https://example.com/external) and [Internal](https://example.com/resolved)');
});

it('resolves statamic entry links with query strings and fragments', function () {
    $converter = app(BardToMarkdown::class);

    $entry = mock(EntryContract::class);
    $entry->shouldReceive('absoluteUrl')->once()->andReturn('https://example.com/guides');

    Entry::shouldReceive('find')->once()->with('entry-guides')->andReturn($entry);

    $nodes = [[
        'type' => 'paragraph',
        'content' => [[
            'type' => 'text',
            'text' => 'Guides',
            'marks' => [[
                'type' => 'link',
                'attrs' => ['href' => 'statamic://entry::entry-guides?topic=bard#links'],
            ]],
        ]],
    ]];

    expect($converter->convert($nodes))
        ->toBe('[Guides](https://example.com/guides?topic=bard#links)');
});

it('falls back to # when a statamic entry link cannot be resolved', function () {
    $converter = app(BardToMarkdown::class);

    Entry::shouldReceive('find')->once()->with('missing-entry')->andReturnNull();

    $nodes = [[
        'type' => 'paragraph',
        'content' => [[
            'type' => 'text',
            'text' => 'Broken',
            'marks' => [[
                'type' => 'link',
                'attrs' => ['href' => 'statamic://entry::missing-entry'],
            ]],
        ]],
    ]];

    expect($converter->convert($nodes))->toBe('[Broken](#)');
});

it('renders unordered and ordered lists from list item paragraphs', function () {
    $converter = app(BardToMarkdown::class);

    $nodes = [
        [
            'type' => 'bulletList',
            'content' => [
                [
                    'type' => 'listItem',
                    'content' => [[
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'First bullet']],
                    ]],
                ],
                [
                    'type' => 'listItem',
                    'content' => [[
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'Second bullet']],
                    ]],
                ],
            ],
        ],
        [
            'type' => 'orderedList',
            'content' => [
                [
                    'type' => 'listItem',
                    'content' => [[
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'First ordered']],
                    ]],
                ],
                [
                    'type' => 'listItem',
                    'content' => [[
                        'type' => 'paragraph',
                        'content' => [['type' => 'text', 'text' => 'Second ordered']],
                    ]],
                ],
            ],
        ],
    ];

    expect($converter->convert($nodes))
        ->toBe("- First bullet\n- Second bullet\n\n1. First ordered\n2. Second ordered");
});

it('renders code blocks with and without a language', function () {
    $converter = app(BardToMarkdown::class);

    $nodes = [
        [
            'type' => 'codeBlock',
            'attrs' => ['language' => 'php'],
            'content' => [
                ['type' => 'text', 'text' => '<?php'],
                ['type' => 'text', 'text' => "\necho 'Hello';"],
            ],
        ],
        [
            'type' => 'codeBlock',
            'content' => [
                ['type' => 'text', 'text' => 'plain text'],
            ],
        ],
    ];

    expect($converter->convert($nodes))->toBe("```php\n<?php\necho 'Hello';\n```\n\n```\nplain text\n```");
});

it('renders blockquotes and prefixes every output line', function () {
    $converter = app(BardToMarkdown::class);

    $nodes = [[
        'type' => 'blockquote',
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [
                    ['type' => 'text', 'text' => 'Quoted line 1'],
                    ['type' => 'hardBreak'],
                    ['type' => 'text', 'text' => 'Quoted line 2'],
                ],
            ],
            [
                'type' => 'heading',
                'attrs' => ['level' => 3],
                'content' => [['type' => 'text', 'text' => 'Quoted heading']],
            ],
        ],
    ]];

    expect($converter->convert($nodes))
        ->toBe("> Quoted line 1\n> Quoted line 2\n>\n> ### Quoted heading");
});
