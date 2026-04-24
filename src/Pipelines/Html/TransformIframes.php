<?php

namespace Rapidez\Statamic\Pipelines\Html;

use Closure;

class TransformIframes
{
    public function handle(string $html, Closure $next): string
    {
        return $next(preg_replace_callback('#<iframe[^>]*src="(https?://[^"]+)"[^>]*>(?:</iframe>)?#', $this->transformIframe(...), $html));
    }

    /**
     * @param array<int, string> $matches
     */
    private function transformIframe(array $matches): string
    {
        $value = (string) $matches[1];

        if (str_contains($value, 'youtube.com/embed/')) {
            $value = str_replace('youtube.com/embed/', 'youtube.com/watch?v=', $value);
        }

        return '<a href="'.$value.'">'.$value.'</a>';
    }
}
