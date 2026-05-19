<?php

namespace Rapidez\Statamic\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Rapidez\Core\Models\Category as CoreCategory;
use Rapidez\Core\Models\Attribute;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Rapidez\Statamic\Models\Traits\HasContentEntry;
use Rapidez\Statamic\Observers\RunwayObserver;
use Rapidez\Statamic\Models\QueryBuilder\EntryFieldSortableBuilder;

#[ObservedBy([RunwayObserver::class])]
class Category extends CoreCategory
{
    use HasRunwayResource, HasContentEntry;

    public string $linkField = 'linked_category';
    public string $collection = 'categories';

    protected static function booting(): void
    {
        parent::booting();

        static::addGlobalScope(fn ($builder) => $builder->with('entry'));
    }

    public function newEloquentBuilder($query): Builder
    {
        return new EntryFieldSortableBuilder($query);
    }

    public function getColumnForField(string $field): string
    {
        return match ($field) {
            'name' => 'category_name_eav.value',
            'entity_id' => 'catalog_category_entity.entity_id',
            default => parent::getColumnForField($field),
        };
    }

    #[Scope]
    protected function runwayListing(Builder $query): void
    {
        $nameAttributeId = Attribute::getCached()['name']->attribute_id ?? null;

        if ($nameAttributeId) {
            $query
                ->addSelect('catalog_category_entity.*')
                ->leftJoin(
                    'catalog_category_entity_varchar as category_name_eav',
                    function ($join) use ($nameAttributeId): void {
                        $join->on('category_name_eav.entity_id', '=', 'catalog_category_entity.entity_id')
                            ->where('category_name_eav.attribute_id', $nameAttributeId)
                            ->where('category_name_eav.store_id', 0);
                    }
                )
                ->addSelect('category_name_eav.value as name');
        }
    }

    #[Scope]
    protected function runwaySearch(Builder $query, string $searchQuery): void
    {
        $term = '%'.$searchQuery.'%';

        $query->where(function (Builder $q) use ($term, $searchQuery): void {
            $q->whereAttribute('name', 'like', $term);

            if (is_numeric($searchQuery)) {
                $q->orWhere($q->getModel()->getQualifiedKeyName(), $searchQuery);
            }
        });
    }
}
