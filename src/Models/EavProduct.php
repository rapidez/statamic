<?php

namespace Rapidez\Statamic\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Rapidez\Core\Enums\EntityType;
use Rapidez\Core\Models\Model;
use Rapidez\Core\Models\Scopes\Product\EnabledScope;
use Rapidez\Core\Models\Scopes\Product\ForCurrentWebsiteScope;
use Rapidez\Core\Models\Scopes\Product\SupportedScope;
use Rapidez\Core\Models\Traits\HasCustomAttributes;

abstract class EavProduct extends Model
{
    use HasCustomAttributes;

    protected $table = 'catalog_product_entity';
    protected $primaryKey = 'entity_id';
    protected $entityTypeId = EntityType::Product->value;

    protected static function booted(): void
    {
        static::addGlobalScope(EnabledScope::class);
        static::addGlobalScope(SupportedScope::class);
        static::addGlobalScope(ForCurrentWebsiteScope::class);
        static::withCustomAttributes();
    }

    protected function modifyRelation(HasMany $relation): HasMany
    {
        return $relation->leftJoin('catalog_eav_attribute', 'catalog_eav_attribute.attribute_id', '=', $relation->qualifyColumn('attribute_id'));
    }
}
