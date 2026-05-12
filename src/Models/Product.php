<?php

namespace Rapidez\Statamic\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Rapidez\Core\Models\Product as CoreProduct;
use Rapidez\Statamic\Models\Traits\HasContentEntry;
use Rapidez\Statamic\Observers\RunwayObserver;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Support\Facades\DB;
use Rapidez\Core\Models\Attribute as EavAttribute;
use Illuminate\Database\Eloquent\Casts\Attribute;

#[ObservedBy([RunwayObserver::class])]
class Product extends CoreProduct
{
    use HasRunwayResource, HasContentEntry;

    protected $primaryKey = 'entity_id';

    public string $linkField = 'linked_product';
    public string $linkKey = 'sku';
    public string $collection = 'products';

    protected static function booting(): void
    {
        parent::booting();

        static::addGlobalScope(function (Builder $builder) {
            $builder->whereInAttribute('visibility', config('rapidez.statamic.runway.product_visibility'));
        });

        static::addGlobalScope(fn ($builder) => $builder->with('entry'));
    }

    #[Override]
    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('runwayMagentoNameColumn', function (Builder $builder): void {
            $attributes = EavAttribute::getCached();
            $nameAttribute = $attributes['name'] ?? null;

            if (! $nameAttribute) {
                return;
            }

            $table = $builder->getModel()->getTable();

            $builder->select($table.'.*');

            $nameSubquery = DB::table($table.'_varchar')
                ->where('attribute_id', $nameAttribute->attribute_id)
                ->where('store_id', config('rapidez.store'))
                ->selectRaw('entity_id as name_entity_id, value as magento_name_value');

            $builder
                ->leftJoinSub($nameSubquery, 'magento_name_attr', function ($join) use ($table): void {
                    $join->on('magento_name_attr.name_entity_id', '=', $table.'.entity_id');
                })
                ->addSelect(DB::raw('magento_name_attr.magento_name_value as name'));
        });
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->value('name'));
    }

    /**
     * Runway's default search only includes columns on the entity table; product names are EAV.
     */
    #[Scope]
    protected function runwaySearch(Builder $query, string $searchQuery): void
    {
        $term = '%'.$searchQuery.'%';
        $table = $query->getModel()->getTable();

        $query->where(function (Builder $q) use ($term, $searchQuery, $table): void {
            $q->whereAttribute('name', 'like', $term)
                ->orWhere($table.'.sku', 'like', $term);

            if (is_numeric($searchQuery)) {
                $q->orWhere($q->getModel()->getQualifiedKeyName(), $searchQuery);
            }
        });
    }
}
