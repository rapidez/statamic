<?php

namespace Rapidez\Statamic\Models;

use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Model;
use Rapidez\Statamic\Facades\RapidezStatamic;

class Category extends Model
{
    use HasRunwayResource;
    
    protected $primaryKey = 'entity_id';

    public function getTable()
    {
        return 'catalog_category_flat_store_'.RapidezStatamic::getCurrentStoreId();
    }
}
