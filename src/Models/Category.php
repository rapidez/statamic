<?php

namespace Rapidez\Statamic\Models;

use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Site;

class Category extends Model
{
    protected $primaryKey = 'entity_id';

    public function getTable()
    {
        return 'catalog_category_flat_store_'.Site::selected()->attributes['magento_store_id'];
    }
}
