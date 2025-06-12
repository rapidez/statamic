<?php

namespace Rapidez\Statamic\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Rapidez\Core\Facades\Rapidez;
use Rapidez\Core\Models\Attribute;
use Rapidez\Statamic\Models\Traits\HasContentEntry;
use Rapidez\Statamic\Observers\RunwayObserver;
use Rapidez\Statamic\Facades\RapidezStatamic;

#[ObservedBy([RunwayObserver::class])]
class Brand extends Model
{
    use HasRunwayResource, HasContentEntry;

    protected $table = 'eav_attribute_option';

    protected $primaryKey = 'option_id';

    public string $linkField = 'linked_brand';
    public string $collection = 'brands';

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
                         ->where('store_value.store_id', RapidezStatamic::getCurrentStoreId());
                })
                ->where('attribute_id', config('rapidez.statamic.runway.brand_attribute_id'));

            if (! config('rapidez.statamic.runway.show_brands_without_products')) {
                Rapidez::withStore(RapidezStatamic::getCurrentStoreId(), fn() => $builder->has('products'));
            }
        });
    }

    public function products(): HasMany
    {
        $brandAttribute = Cache::remember(
            'runway-brand-attribute',
            now()->addDay(),
            fn() => Attribute::find(config('rapidez.statamic.runway.brand_attribute_id'))?->code ?? 'manufacturer',
        );

        return $this->hasMany(Product::class, $brandAttribute, 'option_id');
    }
}
