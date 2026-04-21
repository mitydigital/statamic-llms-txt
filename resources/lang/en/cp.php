<?php

return [
    'nav' => 'llms.txt',

    'name' => 'llms.txt for Statamic',
    'permission' => [
        'label' => 'Manage llms.txt for Statamic',
        'description' => 'Grants access to author llms.txt for Statamic content.',
    ],

    'content' => [
        'display' => 'llms.txt content',
        'instructions' => 'Author your humanly-curated llms.txt content to help AI best understand your site and its content.',

        'validation' => [
            'has_single_h1_only' => 'You can only have one H1 at the root level.',

            'has_single_level_lists_only' => 'You cannot have nested lists: the structure of a llms.txt file only supports single level lists.',

            'has_valid_heading_hierarchy' => 'Your heading ":text" is H:level, and cannot follow ":lastLevelText", a H:lastLevel',

            'is_root_h1' => 'Your llms.txt must start with a H1.',
        ],
    ],
];
