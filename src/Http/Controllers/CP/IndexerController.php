<?php

namespace Rapidez\Statamic\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Statamic\Http\Controllers\CP\CpController;

class IndexerController extends CpController
{
    public function run(): JsonResponse
    {
        $this->authorize('run rapidez indexer');

        if (config('queue.default') === 'redis') {
            Artisan::queue('rapidez:index');

            return response()->json([
                'message' => __('rapidez-statamic::messages.indexer_success_queued'),
                'queued' => true,
            ]);
        }

        Artisan::call('rapidez:index');

        return response()->json([
            'message' => __('rapidez-statamic::messages.indexer_success_completed'),
            'queued' => false,
        ]);
    }
}
