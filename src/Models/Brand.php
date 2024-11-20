<?php

namespace Rapidez\Statamic\Models;

use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Statamic\Facades\Site;
use Statamic\Statamic;

class Brand extends Model
{
    use HasRunwayResource;

    protected $table = 'eav_attribute_option';

    protected $primaryKey = 'option_id';

    protected static function booting()
    {
        static::addGlobalScope(function (Builder $builder) {
            $renamedAdmin = DB::table('eav_attribute_option_value')
                ->select(['value AS value_admin', 'option_id AS sub_option_id', 'store_id']);

            $renamedStore = DB::table('eav_attribute_option_value')
                ->select(['value AS value_store', 'option_id AS sub_option_id', 'store_id']);

            $builder
                ->select('eav_attribute_option.option_id AS option_id')
                ->addSelect('eav_attribute_option.sort_order')
                ->addSelect('value_admin')
                ->selectRaw('IFNULL(value_store, value_admin) AS value_store')
                ->leftJoinSub($renamedAdmin, 'admin_value', function ($join) {
                    $join->on('admin_value.sub_option_id', '=', 'eav_attribute_option.option_id')
                         ->where('admin_value.store_id', 0);
                })
                ->leftJoinSub($renamedStore, 'store_value', function ($join) {
                    $join->on('store_value.sub_option_id', '=', 'eav_attribute_option.option_id')
                         ->where('store_value.store_id', (Statamic::isCpRoute() ? (Site::selected()->attributes['magento_store_id'] ?? '1') : (Site::current()->attributes['magento_store_id'] ?? '1')));
                })
                ->where('attribute_id', config('rapidez.statamic.runway.brand_attribute_id'));
        });
    }
}
