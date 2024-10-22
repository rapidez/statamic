<?php

namespace Rapidez\Statamic\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Rapidez\Statamic\Collections\Products;
use Rapidez\Statamic\Models\Traits\HasContentEntry;
use Rapidez\Statamic\Observers\RunwayObserver;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Statamic\Facades\Site;
use Statamic\Statamic;

#[ObservedBy([RunwayObserver::class])]
class Product extends Model
{
    use HasRunwayResource, HasContentEntry;
    
    protected $primaryKey = 'sku';
    protected $keyType = 'string';
    
    public $linkField = 'linked_product';
    public $collection = 'products';

    protected static function booting()
    {
        static::addGlobalScope(function (Builder $builder) {
            $builder->whereIn('visibility', config('rapidez.statamic.runway.product_visibility'));
        });
    }

    public function getTable()
    {
        return 'catalog_product_flat_' . (Statamic::isCpRoute()
            ? (Site::selected()->attributes['magento_store_id'] ?? '1')
            : (Site::current()->attributes['magento_store_id'] ?? '1')
        );
    }
}
