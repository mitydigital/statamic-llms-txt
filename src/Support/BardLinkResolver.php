<?php

namespace MityDigital\StatamicLlmsTxt\Support;

use Statamic\Facades\Entry;
use Statamic\Facades\Site;

class BardLinkResolver
{
    /**
     * @param  array<int, array<string, mixed>>  $content
     * @return array<int, array<string, mixed>>
     */
    public function resolve(array $content): array
    {
        if (! config('statamic-llms-txt.convert_urls_to_entries.enabled', true)) {
            return $content;
        }

        return array_map(fn (array $node): array => $this->resolveNode($node), $content);
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>
     */
    protected function resolveNode(array $node): array
    {
        if (isset($node['marks']) && is_array($node['marks'])) {
            $node['marks'] = array_map(fn (array $mark): array => $this->resolveMark($mark), $node['marks']);
        }

        if (isset($node['content']) && is_array($node['content'])) {
            $node['content'] = array_map(fn (array $child): array => $this->resolveNode($child), $node['content']);
        }

        return $node;
    }

    /**
     * @param  array<string, mixed>  $mark
     * @return array<string, mixed>
     */
    protected function resolveMark(array $mark): array
    {
        if (($mark['type'] ?? null) !== 'link' || ! is_string($mark['attrs']['href'] ?? null)) {
            return $mark;
        }

        $mark['attrs']['href'] = $this->resolveHref($mark['attrs']['href']);

        return $mark;
    }

    protected function resolveHref(string $href): string
    {
        if (! $this->isResolvableHref($href)) {
            return $href;
        }

        $absoluteUrl = $this->absoluteUrl($href);

        if ($absoluteUrl === null || ! $this->matchesAllowedBaseUrl($absoluteUrl)) {
            return $href;
        }

        $site = Site::findByUrl($absoluteUrl) ?? Site::default();

        if (! $site || ! $entry = Entry::findByUri($this->uri($absoluteUrl, $site), $site->handle())) {
            return $href;
        }

        return 'statamic://entry::'.$entry->id().$this->appends($absoluteUrl);
    }

    protected function isResolvableHref(string $href): bool
    {
        if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'statamic://')) {
            return false;
        }

        $scheme = parse_url($href, PHP_URL_SCHEME);

        return $scheme === null || in_array(strtolower($scheme), ['http', 'https'], true);
    }

    protected function absoluteUrl(string $href): ?string
    {
        if (parse_url($href, PHP_URL_HOST) !== null) {
            return $href;
        }

        $baseUrl = rtrim((string) config('app.url'), '/');

        if ($baseUrl === '') {
            return null;
        }

        return $baseUrl.'/'.ltrim($href, '/');
    }

    protected function matchesAllowedBaseUrl(string $url): bool
    {
        return collect([config('app.url'), ...config('statamic-llms-txt.convert_urls_to_entries.urls', [])])
            ->filter(fn (mixed $baseUrl): bool => is_string($baseUrl) && $baseUrl !== '')
            ->contains(fn (string $baseUrl): bool => $this->matchesBaseUrl($url, $baseUrl));
    }

    protected function matchesBaseUrl(string $url, string $baseUrl): bool
    {
        $urlParts = parse_url($url);
        $baseUrlParts = parse_url($baseUrl);

        if (! is_array($urlParts) || ! is_array($baseUrlParts)) {
            return false;
        }

        foreach (['scheme', 'host', 'port'] as $part) {
            if (($urlParts[$part] ?? null) !== ($baseUrlParts[$part] ?? null)) {
                return false;
            }
        }

        $basePath = rtrim($baseUrlParts['path'] ?? '', '/');
        $urlPath = $urlParts['path'] ?? '/';

        return $basePath === '' || $urlPath === $basePath || str_starts_with($urlPath, $basePath.'/');
    }

    protected function uri(string $url, object $site): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $sitePath = rtrim((string) parse_url($site->absoluteUrl(), PHP_URL_PATH), '/');

        if ($sitePath !== '' && ($path === $sitePath || str_starts_with($path, $sitePath.'/'))) {
            $path = substr($path, strlen($sitePath));
        }

        return '/'.ltrim($path, '/');
    }

    protected function appends(string $url): string
    {
        $query = parse_url($url, PHP_URL_QUERY);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);

        return ($query === null ? '' : '?'.$query)
            .($fragment === null ? '' : '#'.$fragment);
    }
}
