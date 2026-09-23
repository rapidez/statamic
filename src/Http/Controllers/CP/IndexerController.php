<?php

namespace Rapidez\Statamic\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;
use Rapidez\Statamic\Support\IndexerFilters;
use Statamic\Http\Controllers\CP\CpController;

class IndexerController extends CpController
{
    public function run(Request $request): JsonResponse
    {
        $this->authorize('run rapidez indexer');

        $validated = $request->validate([
            'types' => ['nullable', 'array'],
            'types.*' => ['string', Rule::in(IndexerFilters::typeValues())],
            'stores' => ['nullable', 'array'],
            'stores.*' => ['string', Rule::in(IndexerFilters::storeValues())],
        ]);

        $options = array_filter([
            '--types' => filled($validated['types'] ?? null) ? implode(',', $validated['types']) : null,
            '--store' => filled($validated['stores'] ?? null) ? implode(',', $validated['stores']) : null,
        ]);

        if (config('queue.default') === 'redis') {
            Artisan::queue('rapidez:index', $options);

            return response()->json([
                'message' => __('rapidez-statamic::messages.indexer.success.queued'),
                'queued' => true,
            ]);
        }

        Artisan::call('rapidez:index', $options);

        return response()->json([
            'message' => __('rapidez-statamic::messages.indexer.success.completed'),
            'queued' => false,
        ]);
    }
}
