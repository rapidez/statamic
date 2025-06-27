<?php

namespace Rapidez\Statamic\Models;

use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Rapidez\Statamic\Facades\RapidezStatamic;

class Product extends Model
{
    use HasRunwayResource;
    
    protected $primaryKey = 'sku';
    protected $keyType = 'string';

    protected static function booting()
    {
        static::addGlobalScope(function (Builder $builder) {
            $builder->whereIn('visibility', config('rapidez.statamic.runway.product_visibility'));
        });
    }

    public function getTable()
    {
        return 'catalog_product_flat_' . RapidezStatamic::getCurrentStoreId();
    }
}
