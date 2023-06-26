<?php

namespace Rapidez\Statamic\Models;

use DoubleThreeDigital\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Site;

class Category extends Model
{
    use HasRunwayResource;
    
    protected $primaryKey = 'entity_id';

    public function getTable()
    {
        return 'catalog_category_flat_store_'.Site::selected()->attributes['magento_store_id'];
    }
}
