<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Content configuration
    |--------------------------------------------------------------------------
    |
    | The main configuration is a Bard field, and may need some additional
    | configuration based on what buttons you want to use.
    |
    */

    'content' => [

        /*
        |--------------------------------------------------------------------------
        | Link Collections
        |--------------------------------------------------------------------------
        |
        | Optional for the 'anchor' button.
        |
        | Limits the Collections that appear in the Entry browser when adding a
        | link to an Entry. Should be an array of Collection handles, for example:
        |
        | 'link_collections' => [
        |     'pages',
        |     'blog',
        | ],
        |
        | Set to 'null' to not define link collections.
        |
        */

        'link_collections' => null,

        /*
        |--------------------------------------------------------------------------
        | Toolbar mode
        |--------------------------------------------------------------------------
        |
        | Choose how you would like the Bard toolbar to appear. Can either be
        | "fixed" or "floating" - pick your favourite.
        |
        */

        'toolbar_mode' => 'fixed',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache configuration
    |--------------------------------------------------------------------------
    |
    | Your rendered llms.txt file can be cached. The cache will automatically
    | be flushed when your llms.txt settings are changed.
    |
    */

    'cache' => [

        /*
        |--------------------------------------------------------------------------
        | Enabled
        |--------------------------------------------------------------------------
        |
        | With caching disabled, your llms.txt will be generated on every request.
        |
        */

        'enabled' => true,

        /*
        |--------------------------------------------------------------------------
        | Duration
        |--------------------------------------------------------------------------
        |
        | The duration of your llms.txt cache, in minutes.
        |
        | Set to `null` to cache forever.
        |
        */

        'duration' => null,

        /*
        |--------------------------------------------------------------------------
        | Key
        |--------------------------------------------------------------------------
        |
        | The cache key where your llms.txt content will be cached.
        |
        */

        'key' => 'mitydigital-statamic-llms-txt',

    ],

];
