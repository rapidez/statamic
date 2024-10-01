<?php

namespace Rapidez\Statamic\Models;

use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Site;
use Statamic\Statamic;

class Category extends Model
{
    use HasRunwayResource;
    
    protected $primaryKey = 'entity_id';

    public function getTable()
    {
        return 'catalog_category_flat_store_'.once(fn() => (Statamic::isCpRoute() ? (Site::selected()->attributes['magento_store_id'] ?? '1') : (Site::current()->attributes['magento_store_id'] ?? '1')));
    }
}
