<?php

namespace Rapidez\Statamic\Pipelines\Markdown;

use Closure;

class TransformImages
{
    public function handle(string $markdown, Closure $next): string
    {
        $markdown = str_replace('![]()', '', $markdown);
        return $next(preg_replace_callback('#(https?://.+\/directive\/___directive\/[^\)"]+)#', $this->transformImage(...), $markdown));
    }

    /**
     * @param array<int, string> $matches
     */
    private function transformImage(array $matches): string
    {
        $value = (string) $matches[1];
        if (!str_contains($value, '/directive/___directive/')) {
            return $value;
        }

        $parts = explode('/', $value);
        $key = array_search("___directive", $parts, true);
        if ($key !== false) {
            $value = $parts[$key+1];
            $value = base64_decode(strtr($value, '-_,', '+/='));

            $parts = explode('"', $value);
            $key = array_search("{{media url=", $parts, true);
            $value = $parts[$key+1];
            $value = Str::finish(config('rapidez.media_url'), '/') . ltrim($value, '/');
        }

        return $value;
    }
}
