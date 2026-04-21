<?php

use Illuminate\Support\Facades\Validator;
use MityDigital\StatamicLlmsTxt\Rules\IsRootH1;

it('passes when the first node is an h1 heading', function () {
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
        ['content' => [new IsRootH1]],
    );

    expect($validator->passes())->toBeTrue();
});

it('fails when the first node is not an h1 heading', function () {
    $value = [
        [
            'type' => 'paragraph',
            'content' => [
                ['type' => 'text', 'text' => 'Intro'],
            ],
        ],
        [
            'type' => 'heading',
            'attrs' => ['level' => 1],
            'content' => [
                ['type' => 'text', 'text' => 'Title'],
            ],
        ],
    ];

    $validator = Validator::make(
        ['content' => $value],
        ['content' => [new IsRootH1]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('content'))
        ->toBe(__('statamic-llms-txt::cp.content.validation.is_root_h1'));
});

it('ignores non-array input', function () {
    $validator = Validator::make(
        ['content' => 'not-an-array'],
        ['content' => [new IsRootH1]],
    );

    expect($validator->passes())->toBeTrue();
});
