<?php

use Illuminate\Support\Facades\Validator;
use MityDigital\StatamicLlmsTxt\Rules\HasSingleLevelListsOnly;

it('passes when lists are single-level only', function () {
    $value = [
        [
            'type' => 'bulletList',
            'content' => [
                [
                    'type' => 'listItem',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [
                                ['type' => 'text', 'text' => 'Item one'],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'listItem',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [
                                ['type' => 'text', 'text' => 'Item two'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $validator = Validator::make(
        ['content' => $value],
        ['content' => [new HasSingleLevelListsOnly]],
    );

    expect($validator->passes())->toBeTrue();
});

it('fails when a list item contains a nested list', function () {
    $value = [
        [
            'type' => 'bulletList',
            'content' => [
                [
                    'type' => 'listItem',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [
                                ['type' => 'text', 'text' => 'Parent item'],
                            ],
                        ],
                        [
                            'type' => 'bulletList',
                            'content' => [
                                [
                                    'type' => 'listItem',
                                    'content' => [
                                        [
                                            'type' => 'paragraph',
                                            'content' => [
                                                ['type' => 'text', 'text' => 'Nested item'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $validator = Validator::make(
        ['content' => $value],
        ['content' => [new HasSingleLevelListsOnly]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('content'))
        ->toBe(__('statamic-llms-txt::cp.content.validation.has_single_level_lists_only'));
});

it('ignores non-array input', function () {
    $validator = Validator::make(
        ['content' => 'not-an-array'],
        ['content' => [new HasSingleLevelListsOnly]],
    );

    expect($validator->passes())->toBeTrue();
});
