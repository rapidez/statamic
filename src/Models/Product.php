<?php

namespace Rapidez\Statamic\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Rapidez\Core\Models\Product as CoreProduct;
use Rapidez\Statamic\Models\Traits\HasContentEntry;
use Rapidez\Statamic\Observers\RunwayObserver;
use StatamicRadPack\Runway\Traits\HasRunwayResource;

#[ObservedBy([RunwayObserver::class])]
class Product extends CoreProduct
{
    use HasRunwayResource, HasContentEntry;

    protected $primaryKey = 'sku';
    protected $keyType = 'string';
    protected $with = ['entry'];

    public string $linkField = 'linked_product';
    public string $collection = 'products';

    protected static function booting()
    {
        static::addGlobalScope(function (Builder $builder) {
            $builder->whereInAttribute('visibility', config('rapidez.statamic.runway.product_visibility'));
        });
    }
}
