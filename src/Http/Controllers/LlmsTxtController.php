<?php

namespace MityDigital\StatamicLlmsTxt\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use MityDigital\StatamicLlmsTxt\Support\BardToMarkdown;
use Statamic\Facades\Addon;

class LlmsTxtController
{
    public function __invoke()
    {
        if (config('statamic-llms-txt.cache.enabled', true)) {
            $ttl = config('statamic-llms-txt.cache.duration', null);

            if ($ttl === null) {
                $markdown = Cache::rememberForever(
                    config('statamic-llms-txt.cache.key', 'mitydigital-statamic-llms-txt'),
                    fn () => $this->getMarkdown()
                );
            } else {
                $markdown = Cache::remember(
                    config('statamic-llms-txt.cache.key', 'mitydigital-statamic-llms-txt'),
                    Carbon::now()->addMinutes($ttl),
                    fn () => $this->getMarkdown()
                );
            }
        } else {
            $markdown = $this->getMarkdown();
        }
        $markdown = $this->getMarkdown();
        return response($markdown, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    protected function getMarkdown(): string
    {
        $addon = Addon::get('mitydigital/statamic-llms-txt');

        return app(BardToMarkdown::class)
            ->convert($addon->settings()->get('content'));
    }
}
