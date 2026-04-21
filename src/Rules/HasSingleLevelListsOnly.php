<?php

namespace MityDigital\StatamicLlmsTxt\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HasSingleLevelListsOnly implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            return;
        }

        if ($this->hasNestedList($value)) {
            $fail(__('statamic-llms-txt::cp.content.validation.has_single_level_lists_only'));
        }
    }

    private function hasNestedList(array $nodes, bool $insideListItem = false): bool
    {
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $type = $node['type'] ?? null;

            // If we're inside a list item and we find another list → invalid
            if ($insideListItem && in_array($type, ['bulletList', 'orderedList'], true)) {
                return true;
            }

            // If this is a list item, recurse with flag enabled
            if ($type === 'listItem') {
                $content = $node['content'] ?? [];

                if ($this->hasNestedList($content, true)) {
                    return true;
                }

                continue;
            }

            // Otherwise keep walking tree
            if (! empty($node['content']) && is_array($node['content'])) {
                if ($this->hasNestedList($node['content'], $insideListItem)) {
                    return true;
                }
            }
        }

        return false;
    }
}
