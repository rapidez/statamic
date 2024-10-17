<?php

namespace Rapidez\Statamic\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use StatamicRadPack\Runway\Support\Json;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Statamic;

class Product extends Model
{
    use HasRunwayResource;
    
    protected $primaryKey = 'sku';
    protected $keyType = 'string';

    protected static function booting()
    {
        static::addGlobalScope(function (Builder $builder) {
            $builder->whereIn('visibility', config('rapidez.statamic.runway.product_visibility'));
        });

        static::updating(function (Product $product) {
            $productEntry = $product->entry;

            if (!$productEntry) {
                $productEntry = Entry::make()
                    ->collection('products')
                    ->locale(Site::selected()->handle())
                    ->data(['linked_product' => $product->sku]);
            }

            $attributes = $product->getDirty();

            foreach ($attributes as $key => $value) {
                // Is there a way we can detect this earlier?
                // So we know what's coming from for example
                // the blueprint as it's defined there.
                if (Json::isJson($value)) {
                    $attributes[$key] = json_decode((string) $value, true);
                }
            }

            $productEntry->merge($attributes)->save();

            return false;
        });

        // Just to make sure you can't create or delete
        static::creating(fn (Product $product) => false);
        static::deleting(fn (Product $product) => false);
    }

    public function getTable()
    {
        return 'catalog_product_flat_' . (Statamic::isCpRoute()
            ? (Site::selected()->attributes['magento_store_id'] ?? '1')
            : (Site::current()->attributes['magento_store_id'] ?? '1')
        );
    }

    public function getAttributes()
    {
        parent::getAttributes();

        // Had some issues with non-existing models and attributes,
        // this fixed that but not sure yet if this is the way.
        if ($this->exists && $entry = ($this->entry ?? '')) {
            $this->attributes = array_merge(
                $entry->data()->toArray(),
                $this->attributes
            );
        }

        return $this->attributes;
    }

    protected function entry(): Attribute
    {
        return Attribute::make(
            get: fn () => Entry::query()
                ->where('collection', 'products')
                ->where('site', Site::selected()->handle())
                ->where('linked_product', $this->sku)
                ->first(),
        )->shouldCache();
    }
}
