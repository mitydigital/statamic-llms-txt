<?php

namespace MityDigital\StatamicLlmsTxt\Support;

use Statamic\Facades\Entry;

class BardToMarkdown
{
    public function convert(array $nodes): string
    {
        return trim(collect($nodes)
            ->map(fn ($node) => $this->renderBlock($node))
            ->filter()
            ->implode("\n\n"));
    }

    protected function renderBlock(array $node): ?string
    {
        return match ($node['type'] ?? null) {
            'heading' => $this->renderHeading($node),
            'paragraph' => $this->renderParagraph($node),
            'bulletList' => $this->renderList($node, '-'),
            'orderedList' => $this->renderList($node, 'ordered'),
            'horizontalRule' => '---',
            'blockquote' => $this->renderBlockquote($node),
            'codeBlock' => $this->renderCodeBlock($node),
            default => null,
        };
    }

    protected function renderHeading(array $node): string
    {
        $level = (int) ($node['attrs']['level'] ?? 2);
        $level = max(1, min($level, 6));

        $text = $this->renderInline($node['content'] ?? []);

        return str_repeat('#', $level).' '.$text;
    }

    protected function renderInline(array $nodes): string
    {
        return collect($nodes)->map(function ($node) {
            return match ($node['type'] ?? null) {
                'text' => $this->applyMarks($node),
                'hardBreak' => "\n",
                default => '',
            };
        })->implode('');
    }

    protected function applyMarks(array $node): string
    {
        $text = $node['text'] ?? '';
        foreach (($node['marks'] ?? []) as $mark) {
            $text = $this->applyMark($text, $mark);
        }

        return $text;
    }

    protected function applyMark(string $text, array $mark): string
    {
        return match ($mark['type'] ?? null) {
            'code' => "`{$text}`",
            'bold' => "**{$text}**",
            'italic' => "*{$text}*",
            'underline' => "<u>{$text}</u>",
            'strikethrough' => "~~{$text}~~",
            'link' => $this->renderLink($text, $mark),
            default => $text,
        };
    }

    protected function renderLink(string $text, array $mark): string
    {
        $href = $mark['attrs']['href'] ?? '#';

        return "[{$text}]({$this->resolveLink($href)})";
    }

    protected function resolveLink(string $href): string
    {
        if (str_starts_with($href, 'statamic://entry::')) {
            $entryReference = str_replace('statamic://entry::', '', $href);
            $queryOrFragmentPosition = strcspn($entryReference, '?#');
            $id = substr($entryReference, 0, $queryOrFragmentPosition);
            $queryOrFragment = substr($entryReference, $queryOrFragmentPosition);
            $entry = Entry::find($id);

            return $entry ? $entry->absoluteUrl().$queryOrFragment : '#';
        }

        return $href;
    }

    protected function renderParagraph(array $node): string
    {
        return $this->renderInline($node['content'] ?? []);
    }

    protected function renderList(array $node, string $type): string
    {
        $items = $node['content'] ?? [];

        return collect($items)
            ->map(function ($item, $index) use ($type) {
                $text = $this->renderInlineFromListItem($item);
                $prefix = $type === 'ordered' ? ($index + 1).'.' : '-';

                return "{$prefix} {$text}";
            })
            ->implode("\n");
    }

    protected function renderInlineFromListItem(array $item): string
    {
        $content = $item['content'] ?? [];

        return collect($content)
            ->map(function ($node) {
                // list items usually wrap paragraphs
                if (($node['type'] ?? null) === 'paragraph') {
                    return $this->renderInline($node['content'] ?? []);
                }

                return '';
            })
            ->implode('');
    }

    protected function renderBlockquote(array $node): string
    {
        $content = collect($node['content'] ?? [])
            ->map(fn ($child) => $this->renderBlock($child))
            ->filter()
            ->implode("\n\n");

        $lines = preg_split("/\n/", $content);

        return collect($lines)
            ->map(fn ($line) => $line === '' ? '>' : '> '.$line)
            ->implode("\n");
    }

    protected function renderCodeBlock(array $node): string
    {
        $language = $node['attrs']['language'] ?? '';
        $text = collect($node['content'] ?? [])
            ->map(fn ($n) => $n['text'] ?? '')
            ->implode('');

        return "```{$language}\n{$text}\n```";
    }
}
