<?php

namespace MityDigital\StatamicLlmsTxt\Rules;

use Closure;
use Generator;
use Illuminate\Contracts\Validation\ValidationRule;

class HasValidHeadingHierarchy implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            return;
        }

        $lastLevel = 0;
        $lastHeadingText = null;

        foreach ($this->walk($value) as $node) {
            if (($node['type'] ?? null) !== 'heading') {
                continue;
            }

            $level = (int) ($node['attrs']['level'] ?? 0);
            $text = $this->getHeadingText($node);

            // hierarchy rule: no skipping levels
            if ($lastLevel !== 0 && abs($level - $lastLevel) > 1) {
                $fail(__('statamic-llms-txt::cp.content.validation.has_valid_heading_hierarchy', [
                    'lastLevel' => $lastLevel,
                    'lastLevelText' => $lastHeadingText,
                    'level' => $level,
                    'text' => $text,
                ]));

                return;
            }

            $lastLevel = $level;

            $lastHeadingText = $text;
        }
    }

    private function walk(array $nodes): Generator
    {
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            yield $node;

            if (! empty($node['content']) && is_array($node['content'])) {
                yield from $this->walk($node['content']);
            }
        }
    }

    private function getHeadingText(array $node): string
    {
        $content = $node['content'] ?? [];

        $text = '';

        foreach ($content as $child) {
            if (($child['type'] ?? null) === 'text') {
                $text .= $child['text'] ?? '';
            }
        }

        return trim($text) !== '' ? trim($text) : '[untitled heading]';
    }
}
