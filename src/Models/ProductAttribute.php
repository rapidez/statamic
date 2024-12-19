<?php

namespace Rapidez\Statamic\Models;

use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Rapidez\Statamic\Facades\RapidezStatamic;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Rapidez\Statamic\Observers\ProductAttributeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(ProductAttributeObserver::class)]
class ProductAttribute extends Model
{
    use HasRunwayResource;

    protected $table = 'eav_attribute';
    
    protected $primaryKey = 'attribute_id';

    protected $appends = [
        'options',
        'frontend_label',
        'formatted_options'
    ];

    public static $inputTypes = [
        'text' => 'Text Field',
        'textarea' => 'Text Area',
        'date' => 'Date',
        'boolean' => 'Yes/No',
        'multiselect' => 'Multiple Select',
        'select' => 'Dropdown',
        'price' => 'Price',
        'media_image' => 'Media Image',
        'weee' => 'Fixed Product Tax',
        'swatch_visual' => 'Visual Swatch',
        'swatch_text' => 'Text Swatch',
    ];

    protected static function booting()
    {
        static::addGlobalScope(function (Builder $builder) {
            $builder->where('entity_type_id', function ($query) {
                $query->select('entity_type_id')
                    ->from('eav_entity_type')
                    ->where('entity_type_code', 'catalog_product');
            });

            $builder->leftJoin('eav_attribute_label', function ($join) {
                $join->on('eav_attribute.attribute_id', '=', 'eav_attribute_label.attribute_id')
                    ->where('eav_attribute_label.store_id', RapidezStatamic::getCurrentStoreId());
            });

            $builder->leftJoin('eav_attribute_option', 'eav_attribute.attribute_id', '=', 'eav_attribute_option.attribute_id')
                ->leftJoin('eav_attribute_option_value as admin_value', function ($join) {
                    $join->on('eav_attribute_option.option_id', '=', 'admin_value.option_id')
                        ->where('admin_value.store_id', 0);
                })
                ->leftJoin('eav_attribute_option_value as store_value', function ($join) {
                    $join->on('store_value.option_id', '=', 'eav_attribute_option.option_id')
                        ->where('store_value.store_id', RapidezStatamic::getCurrentStoreId());
                });

            $builder->select([
                'eav_attribute.*',
                'eav_attribute_label.value as store_frontend_label',
                DB::raw('GROUP_CONCAT(DISTINCT COALESCE(store_value.value, admin_value.value)) as option_values'),
                DB::raw('GROUP_CONCAT(DISTINCT eav_attribute_option.option_id) as option_ids')
            ])
            ->groupBy([
                'eav_attribute.attribute_id',
                'eav_attribute.attribute_code',
                'eav_attribute.frontend_input',
                'eav_attribute.frontend_label',
                'eav_attribute_label.value'
            ]);
        });
    }

    public function options(): Attribute
    {
        return Attribute::make(
            get: fn () => collect([$this->option_ids, $this->option_values])
                ->map(fn($value) => collect(explode(',', $value))->filter())
                ->pipe(function (Collection $collections) {
                    $optionIds = $collections[0];
                    $optionValues = $collections[1];
    
                    if ($optionIds->count() === $optionValues->count()) {
                        return $optionIds->combine($optionValues);
                    }
    
                    return [];
                })
        );
    }

   public function formattedOptions(): Attribute
   {
       return Attribute::make(
           get: fn () => empty($this->options) ? '' : 
               collect($this->options)
                   ->map(fn($value, $id) => "{$value} (ID: {$id})")
                   ->join(', ')
       );
   }

   public function frontendLabel(): Attribute
   {
       return Attribute::make(
           get: fn () => $this->store_frontend_label ?? $this->attributes['frontend_label'] ?? ''
       );
   }

    public function getCacheKey(): string
    {
        return "product_attribute_{$this->attribute_id}_store_" . RapidezStatamic::getCurrentStoreId();
    }

    public function scopeVisible($query): void
    {
        $query->where('is_visible', 1);
    }

    public function scopeVisibleOnFront($query): void
    {
        $query->where('is_visible_on_front', 1);
    }

    public function scopeFilterable($query): void
    {
        $query->where('is_filterable', 1);
    }

    public function scopeFilterableInSearch($query): void
    {
        $query->where('is_filterable_in_search', 1);
    }

    public function scopeSearchable($query): void
    {
        $query->where('is_searchable', 1);
    }

    public function scopeUsedInProductListing($query): void
    {
        $query->where('used_in_product_listing', 1);
    }

    public function scopeWithOptions($query): void
    {
        $query->whereIn('frontend_input', ['select', 'multiselect', 'swatch_visual', 'swatch_text']);
    }

    public function scopeByInputType($query, $type): void
    {
        if (!isset(static::$inputTypes[$type])) {
            return;
        }

        $query->where('frontend_input', $type);
    }

    public function attributeOptions(): HasMany
    {
        return $this->hasMany(ProductAttributeOption::class, 'attribute_id', 'attribute_id');
    }
}
