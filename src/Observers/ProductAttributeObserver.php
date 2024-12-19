<?php

namespace Rapidez\Statamic\Observers;

use Illuminate\Support\Facades\Cache;
use Rapidez\Statamic\Models\ProductAttribute;
use Rapidez\Statamic\Models\ProductAttributeOption;

class ProductAttributeObserver
{
    public function saved(ProductAttribute|ProductAttributeOption $model): void
    {
        Cache::forget($model->getCacheKey());
    }

    public function deleted(ProductAttribute|ProductAttributeOption $model): void
    {
        Cache::forget($model->getCacheKey());
    }
}