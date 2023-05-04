<?php

namespace Rapidez\Statamic\Models;

use DoubleThreeDigital\Runway\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Site;

class Brand extends Model
{
    protected $table = 'eav_attribute_option';

    protected $primaryKey = 'option_id';

    protected static function booting()
    {
        static::addGlobalScope(function (Builder $builder) {
            $builder
                ->select('eav_attribute_option.option_id AS option_id')
                ->addSelect('eav_attribute_option.sort_order')
                ->addSelect('admin_value.value AS value_admin')
                ->selectRaw('IFNULL(store_value.value, admin_value.value) AS value_store')
                ->leftJoin('eav_attribute_option_value AS admin_value', function ($join) {
                    $join->on('admin_value.option_id', '=', 'eav_attribute_option.option_id')
                         ->where('admin_value.store_id', 0);
                })
                ->leftJoin('eav_attribute_option_value AS store_value', function ($join) {
                    $join->on('store_value.option_id', '=', 'eav_attribute_option.option_id')
                         ->where('store_value.store_id', Site::selected()->attributes['magento_store_id']);
                })
                ->where('attribute_id', config('rapidez-statamic.runway.brand_attribute_id'));
        });
    }
}
