<?php

use Illuminate\Support\Facades\Route;
use Rapidez\Statamic\Http\Controllers\CP\IndexerController;

Route::post('rapidez/indexer/run', [IndexerController::class, 'run'])
    ->name('rapidez.indexer.run');
