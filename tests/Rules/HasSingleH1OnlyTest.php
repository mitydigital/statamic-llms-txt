<?php

use Illuminate\Support\Facades\Validator;
use MityDigital\StatamicLlmsTxt\Rules\HasSingleH1Only;

it('passes when there is a single root-level h1', function () {
    $value = [
        [
            'type' => 'heading',
            'attrs' => ['level' => 1],
            'content' => [
                ['type' => 'text', 'text' => 'Title'],
            ],
        ],
        [
            'type' => 'paragraph',
            'content' => [
                ['type' => 'text', 'text' => 'Body'],
            ],
        ],
    ];

    $validator = Validator::make(
        ['content' => $value],
        ['content' => [new HasSingleH1Only]],
    );

    expect($validator->passes())->toBeTrue();
});

it('fails when there are multiple root-level h1 headings', function () {
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
            'attrs' => ['level' => 1],
            'content' => [
                ['type' => 'text', 'text' => 'Another Title'],
            ],
        ],
    ];

    $validator = Validator::make(
        ['content' => $value],
        ['content' => [new HasSingleH1Only]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('content'))
        ->toBe(__('statamic-llms-txt::cp.content.validation.has_single_h1_only'));
});

it('ignores non-array input', function () {
    $validator = Validator::make(
        ['content' => 'not-an-array'],
        ['content' => [new HasSingleH1Only]],
    );

    expect($validator->passes())->toBeTrue();
});
