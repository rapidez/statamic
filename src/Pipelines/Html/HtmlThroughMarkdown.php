<?php

namespace Rapidez\Statamic\Pipelines\Html;

use Closure;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Str;
use League\HTMLToMarkdown\Converter\TableConverter;
use League\HTMLToMarkdown\HtmlConverter;

class HtmlThroughMarkdown
{
    private readonly ?HtmlConverter $converter;

    public function __construct(private Pipeline $pipeline)
    {
        $this->converter = new HtmlConverter(['strip_tags' => true]);
        $this->converter->getEnvironment()->addConverter(new TableConverter());
    }

    public function handle(string $html, Closure $next): string
    {
        $markdown = $this->converter->convert($html);
        $markdown = $this->pipeline
            ->send($markdown)
            ->via('handle')
            ->through(config('rapidez.statamic.migration.pipelines.clean_html.markdown', []))
            ->thenReturn();

        return $next(Str::markdown($markdown));
    }
}
