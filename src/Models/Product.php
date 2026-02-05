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

    public string $linkField = 'linked_product';
    public string $linkKey = 'sku';
    public string $collection = 'products';

    protected function getAttributeRelationKey(): string
    {
        return 'entity_id';
    }

    protected static function booting(): void
    {
        parent::booting();

        static::addGlobalScope(function (Builder $builder) {
            $builder->whereInAttribute('visibility', config('rapidez.statamic.runway.product_visibility'));
        });

        static::addGlobalScope(fn ($builder) => $builder->with('entry'));
    }
}
