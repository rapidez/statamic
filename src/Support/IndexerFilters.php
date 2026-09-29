<?php

namespace Rapidez\Statamic\Support;

use Rapidez\Core\Facades\Rapidez;
use Rapidez\Core\Models\Traits\Searchable;

class IndexerFilters
{
    public static function typeOptions(): array
    {
        return collect(config('rapidez.models'))
            ->filter(fn ($class) => in_array(Searchable::class, class_uses_recursive($class)))
            ->map(fn ($class) => [
                'value' => $class::getIndexName(),
                'label' => $class::getIndexName(),
            ])
            ->values()
            ->all();
    }

    public static function storeOptions(): array
    {
        return collect(Rapidez::getStores())
            ->map(fn (array $store) => [
                'value' => (string) $store['store_id'],
                'label' => $store['name'],
            ])
            ->values()
            ->all();
    }

    public static function typeValues(): array
    {
        return collect(static::typeOptions())->pluck('value')->all();
    }

    public static function storeValues(): array
    {
        return collect(static::storeOptions())->pluck('value')->all();
    }
}
