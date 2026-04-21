<?php

namespace MityDigital\StatamicLlmsTxt\Concerns;

trait HasH1Awareness
{
    private function isH1($node): bool
    {
        if (! is_array($node)) {
            return false;
        }

        return ($node['type'] ?? null) === 'heading' && (int) ($node['attrs']['level'] ?? 0) === 1;
    }
}
