<?php

namespace Rapidez\Statamic\Models;

use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Site;

class Product extends Model
{
    protected $primaryKey = 'sku';
    protected $keyType = 'string';

    public function getTable()
    {
        return 'catalog_product_flat_'.Site::selected()->attributes['magento_store_id'];
    }
}
