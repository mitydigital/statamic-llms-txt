<?php

use Illuminate\Support\Facades\Validator;
use MityDigital\StatamicLlmsTxt\Rules\HasValidHeadingHierarchy;

it('passes when heading levels progress without skipping', function () {
    $value = [
        [
            'type' => 'heading',
            'attrs' => ['level' => 1],
            'content' => [
                ['type' => 'text', 'text' => 'Title'],
            ],
        ],
        [
            'type' => 'heading',
            'attrs' => ['level' => 2],
            'content' => [
                ['type' => 'text', 'text' => 'Section'],
            ],
        ],
        [
            'type' => 'heading',
            'attrs' => ['level' => 3],
            'content' => [
                ['type' => 'text', 'text' => 'Subsection'],
            ],
        ],
    ];

    $validator = Validator::make(
        ['content' => $value],
        ['content' => [new HasValidHeadingHierarchy]],
    );

    expect($validator->passes())->toBeTrue();
});

it('fails when heading levels skip by more than one', function () {
    $value = [
        [
            'type' => 'heading',
            'attrs' => ['level' => 1],
            'content' => [
                ['type' => 'text', 'text' => 'Title'],
            ],
        ],
        [
            'type' => 'heading',
            'attrs' => ['level' => 3],
            'content' => [
                ['type' => 'text', 'text' => 'Skipped Section'],
            ],
        ],
    ];

    $validator = Validator::make(
        ['content' => $value],
        ['content' => [new HasValidHeadingHierarchy]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('content'))
        ->toBe(__('statamic-llms-txt::cp.content.validation.has_valid_heading_hierarchy', [
            'lastLevel' => 1,
            'lastLevelText' => 'Title',
            'level' => 3,
            'text' => 'Skipped Section',
        ]));
});

it('ignores non-array input', function () {
    $validator = Validator::make(
        ['content' => 'not-an-array'],
        ['content' => [new HasValidHeadingHierarchy]],
    );

    expect($validator->passes())->toBeTrue();
});
