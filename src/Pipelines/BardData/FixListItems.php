<?php

namespace Rapidez\Statamic\Pipelines\BardData;

use Closure;

class FixListItems
{
    private const array BLOCK_NODE_TYPES = [
        'paragraph', 'heading', 'bulletList', 'orderedList',
        'codeBlock', 'blockquote', 'horizontalRule',
    ];

    public function handle(array $data, Closure $next): array
    {
        return $next($this->fixListItems($data));
    }

    /**
     * Recursively walk any value (array, nested arrays) and fix listItem nodes
     * whose content consists entirely of inline nodes (e.g. bare "text" nodes)
     * by wrapping them in a paragraph node, as Statamic's Bard editor requires.
     */
    public function fixListItems(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }
        
        if (($data['type'] ?? null) === 'listItem' && isset($data['content'])) {
            $data['content'] = $this->wrapInlineContentInParagraph($data['content']);
        }

        foreach ($data as $key => $value) {
            $data[$key] = $this->fixListItems($value);
        }

        return $data;
    }

    /**
     * @param array<int, mixed> $content
     * @return array<int, mixed>
     */
    private function wrapInlineContentInParagraph(array $content): array
    {
        $allInline = collect($content)->every(
            fn (mixed $node): bool => !in_array($node['type'] ?? null, self::BLOCK_NODE_TYPES, true)
        );

        if (!$allInline) {
            return $content;
        }

        return [['type' => 'paragraph', 'content' => $content]];
    }
}
