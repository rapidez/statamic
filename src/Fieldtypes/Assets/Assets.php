<?php

namespace Rapidez\Statamic\Fieldtypes\Assets;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Statamic\Assets\OrderedQueryBuilder;
use Statamic\Fieldtypes\Assets\Assets as StatamicAssets;

class Assets extends StatamicAssets
{
    public function augment($values)
    {
        $values = Arr::wrap($values);

        $single = $this->config('max_files') === 1;

        if ($single && Cache::has($key = 'assets-augment-' . json_encode($values))) {
            return Cache::get($key);
        }

        $ids = collect($values)
            ->map(fn($value) => $this->container()->handle() . '::' . $value)
            ->all();

        $query = $this->container()->queryAssets()->whereIn('path', $values);

        $query = new OrderedQueryBuilder($query, $ids);

        return $single && ! config('statamic.system.always_augment_to_query', false)
            ? Cache::rememberForever($key, fn() => $query->first())
            : $query;
    }
}