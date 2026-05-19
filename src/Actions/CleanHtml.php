<?php

namespace Rapidez\Statamic\Actions;

use Illuminate\Pipeline\Pipeline;

class CleanHtml
{
    public function __construct(private Pipeline $pipeline)
    {
    }
    /**
     * Transform HTML to statamic compatible content
     */
    public function execute(string $html): string
    {
        return $this->pipeline
            ->send($html)
            ->via('handle')
            ->through(config('rapidez.statamic.migration.pipelines.clean_html.html', []))
            ->thenReturn();
    }
}
