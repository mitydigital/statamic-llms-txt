<?php

namespace MityDigital\StatamicLlmsTxt\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MityDigital\StatamicLlmsTxt\Concerns\HasH1Awareness;

class HasSingleH1Only implements ValidationRule
{
    use HasH1Awareness;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            return;
        }

        $h1Count = 0;

        foreach ($value as $node) {
            if (! is_array($node)) {
                continue;
            }

            if ($this->isH1($node)) {
                $h1Count++;
            }
        }

        if ($h1Count > 1) {
            $fail(__('statamic-llms-txt::cp.content.validation.has_single_h1_only'));
        }
    }
}
