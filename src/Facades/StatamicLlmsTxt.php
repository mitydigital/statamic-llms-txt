<?php

namespace MityDigital\StatamicLlmsTxt\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Fields\Blueprint;

/**
 * @method static Blueprint blueprint()
 *
 * @see \MityDigital\StatamicLlmsTxt\Support\StatamicLlmsTxt
 */
class StatamicLlmsTxt extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \MityDigital\StatamicLlmsTxt\Support\StatamicLlmsTxt::class;
    }
}
