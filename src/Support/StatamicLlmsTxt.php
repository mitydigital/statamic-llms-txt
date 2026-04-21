<?php

namespace MityDigital\StatamicLlmsTxt\Support;

use MityDigital\StatamicLlmsTxt\Rules\HasSingleH1Only;
use MityDigital\StatamicLlmsTxt\Rules\HasSingleLevelListsOnly;
use MityDigital\StatamicLlmsTxt\Rules\HasValidHeadingHierarchy;
use MityDigital\StatamicLlmsTxt\Rules\IsRootH1;

class StatamicLlmsTxt
{
    public function blueprint(): array
    {
        return [
            'sections' => [
                'main' => [
                    'fields' => [
                        [
                            'handle' => 'content',
                            'field' => [
                                'display' => __('statamic-llms-txt::cp.content.display'),
                                'instructions' => __('statamic-llms-txt::cp.content.instructions'),
                                'type' => 'bard',
                                'buttons' => [
                                    'h1',
                                    'h2',
                                    'h3',
                                    'h4',
                                    'bold',
                                    'italic',
                                    'quote',
                                    'unorderedlist',
                                    'orderedlist',
                                    'anchor',
                                    'horizontalrule',
                                    'code',
                                    'codeBlock',
                                ],
                                'toolbar_mode' => config('statamic-llms-txt.content.toolbar_mode', 'fixed'),
                                'link_collections' => self::getBardLinkCollections(),
                                'remove_empty_nodes' => true,
                                'validate' => [
                                    'required',
                                    new HasSingleH1Only,
                                    new HasSingleLevelListsOnly,
                                    new HasValidHeadingHierarchy,
                                    new IsRootH1,
                                ],
                                'full_width_setting' => true,
                                'antlers' => true,
                                'select_across_sites' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getBardLinkCollections(): ?array
    {
        $config = config('statamic-llms-txt.content.link_collections');

        if ($config && is_array($config)) {
            return $config;
        }

        return null;
    }
}
