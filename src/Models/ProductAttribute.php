<?php

namespace Rapidez\Statamic\Models;

use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Statamic\Facades\Site;
use Statamic\Statamic;

class ProductAttribute extends Model
{
    use HasRunwayResource;

    protected $table = 'eav_attribute';
    
    protected $primaryKey = 'attribute_id';

    protected $fillable = [
        'attribute_id',
        'attribute_code',
        'entity_type_id',
        'frontend_input',
        'frontend_label',
        'is_required',
        'is_user_defined',
        'is_unique',
        'is_visible',
        'is_searchable',
        'is_filterable',
        'is_comparable',
        'is_visible_on_front',
        'is_html_allowed_on_front',
        'is_used_for_price_rules',
        'is_filterable_in_search',
        'used_in_product_listing',
        'used_for_sort_by',
        'backend_type',
        'backend_model',
        'frontend_model',
        'source_model',
    ];

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
                    ->where('eav_attribute_label.store_id', static::getCurrentStoreId());
            });

            $builder->leftJoin('eav_attribute_option', 'eav_attribute.attribute_id', '=', 'eav_attribute_option.attribute_id')
                ->leftJoin('eav_attribute_option_value as admin_value', function ($join) {
                    $join->on('eav_attribute_option.option_id', '=', 'admin_value.option_id')
                        ->where('admin_value.store_id', 0);
                })
                ->leftJoin('eav_attribute_option_value as store_value', function ($join) {
                    $join->on('store_value.option_id', '=', 'eav_attribute_option.option_id')
                        ->where('store_value.store_id', static::getCurrentStoreId());
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

    protected static function getCurrentStoreId(): string
    {
        return once(fn() => (Statamic::isCpRoute()
            ? (Site::selected()->attributes['magento_store_id'] ?? '1')
            : (Site::current()->attributes['magento_store_id'] ?? '1')
        ));
    }

    public function getOptionsAttribute()
    {
        if (!$this->option_ids || !$this->option_values) {
            return [];
        }

        $optionIds = array_filter(explode(',', $this->option_ids));
        $optionValues = array_filter(explode(',', $this->option_values));

        if (count($optionIds) !== count($optionValues)) {
            return [];
        }

        return array_combine($optionIds, $optionValues);
    }

    public function getFormattedOptionsAttribute()
    {
        if (empty($this->options)) {
            return '';
        }

        return collect($this->options)
            ->map(fn($value, $id) => "{$value} (ID: {$id})")
            ->join(', ');
    }

    public function getFrontendLabelAttribute()
    {
        return $this->store_frontend_label ?? $this->attributes['frontend_label'] ?? '';
    }

    public function getCacheKey(): string
    {
        return "product_attribute_{$this->attribute_id}_store_" . static::getCurrentStoreId();
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', 1);
    }

    public function scopeVisibleOnFront($query)
    {
        return $query->where('is_visible_on_front', 1);
    }

    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', 1);
    }

    public function scopeFilterableInSearch($query)
    {
        return $query->where('is_filterable_in_search', 1);
    }

    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', 1);
    }

    public function scopeUsedInProductListing($query)
    {
        return $query->where('used_in_product_listing', 1);
    }

    public function scopeWithOptions($query)
    {
        return $query->whereIn('frontend_input', ['select', 'multiselect', 'swatch_visual', 'swatch_text']);
    }

    public function scopeByInputType($query, $type)
    {
        if (!isset(static::$inputTypes[$type])) {
            return $query;
        }

        return $query->where('frontend_input', $type);
    }

    public function options()
    {
        return $this->hasMany(ProductAttributeOption::class, 'attribute_id', 'attribute_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            Cache::forget($model->getCacheKey());
        });

        static::deleted(function ($model) {
            Cache::forget($model->getCacheKey());
        });
    }
}
