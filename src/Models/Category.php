<?php

namespace Rapidez\Statamic\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Model;
use Rapidez\Statamic\Models\Traits\HasContentEntry;
use Rapidez\Statamic\Observers\RunwayObserver;
use Rapidez\Statamic\Facades\RapidezStatamic;

#[ObservedBy([RunwayObserver::class])]
class Category extends Model
{
    use HasRunwayResource, HasContentEntry;

    protected $primaryKey = 'entity_id';
    protected $with = ['entry'];

    public string $linkField = 'linked_category';
    public string  $collection = 'categories';

    public function getTable()
    {
        return 'catalog_category_flat_store_'.RapidezStatamic::getCurrentStoreId();
    }
}
