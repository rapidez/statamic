<?php

namespace Rapidez\Statamic\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Model;
use Rapidez\Statamic\Models\Traits\HasContentEntry;
use Statamic\Facades\Site;
use Statamic\Statamic;
use Rapidez\Statamic\Observers\RunwayObserver;

#[ObservedBy([RunwayObserver::class])]
class Category extends Model
{
    use HasRunwayResource, HasContentEntry;
    
    protected $primaryKey = 'entity_id';

    public string $linkField = 'linked_category';
    public string  $collection = 'categories';

    public function getTable()
    {
        return 'catalog_category_flat_store_'.(Statamic::isCpRoute() ? (Site::selected()->attributes['magento_store_id'] ?? '1') : (Site::current()->attributes['magento_store_id'] ?? '1'));
    }
}
