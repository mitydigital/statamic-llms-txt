<?php

namespace MityDigital\StatamicLlmsTxt\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MityDigital\StatamicLlmsTxt\Concerns\HasH1Awareness;

class IsRootH1 implements ValidationRule
{
    use HasH1Awareness;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            return;
        }

        $firstNode = $value[0] ?? null;

        if (! $this->isH1($firstNode)) {
            $fail(__('statamic-llms-txt::cp.content.validation.is_root_h1'));
        }
    }
}
