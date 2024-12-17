<?php

namespace Rapidez\Statamic\Traits;

use Statamic\Facades\Site;
use Statamic\Statamic;

trait StoreIdTrait
{
    protected static function getCurrentStoreId(): string
    {
        return once(fn() => (Statamic::isCpRoute()
            ? (Site::selected()->attributes['magento_store_id'] ?? '1')
            : (Site::current()->attributes['magento_store_id'] ?? '1')
        ));
    }
}