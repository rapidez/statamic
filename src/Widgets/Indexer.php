<?php

namespace Rapidez\Statamic\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Statamic\Facades\User;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;

class Indexer extends Widget
{
    public function component(): ?VueComponent
    {
        if (! User::current()?->can('run rapidez indexer')) {
            return null;
        }

        return VueComponent::render('rapidez-indexer-widget', [
            'title' => __('rapidez-statamic::messages.indexer_widget_title'),
            'runUrl' => cp_route('rapidez.indexer.run'),
            'lastIndexedAt' => $this->lastIndexedAt(),
            'labels' => [
                'description' => __('rapidez-statamic::messages.indexer_description'),
                'run' => __('rapidez-statamic::messages.indexer_run'),
                'last_run' => __('rapidez-statamic::messages.indexer_last_run'),
                'never_run' => __('rapidez-statamic::messages.indexer_never_run'),
                'confirm' => [
                    'title' => __('rapidez-statamic::messages.indexer_confirm_title'),
                    'body' => __('rapidez-statamic::messages.indexer_confirm_body'),
                    'button' => __('rapidez-statamic::messages.indexer_confirm_button'),
                ],
                'success' => [
                    'queued' => __('rapidez-statamic::messages.indexer_success_queued'),
                    'completed' => __('rapidez-statamic::messages.indexer_success_completed'),
                ],
                'error' => __('rapidez-statamic::messages.indexer_error'),
            ],
        ]);
    }

    protected function lastIndexedAt(): ?string
    {
        if (! Storage::disk('local')->exists('/.last-index')) {
            return null;
        }

        try {
            return Carbon::parse(Storage::disk('local')->get('/.last-index'))
                ->timezone(config('app.timezone'))
                ->toIso8601String();
        } catch (\Throwable) {
            return null;
        }
    }
}
