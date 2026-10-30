<?php

namespace Rapidez\Statamic\StaticCaching;

use Illuminate\Support\Arr;
use Rapidez\Core\Facades\Rapidez;
use Statamic\Eloquent\Globals\GlobalSet;
use Statamic\Eloquent\Structures\Nav;
use Statamic\Entries\Entry;
use Statamic\Facades\URL;
use Statamic\Forms\Form;
use Statamic\StaticCaching\DefaultInvalidator;

class CustomInvalidator extends DefaultInvalidator
{
    public function invalidate($item)
    {
        if ($item instanceof GlobalSet || $item instanceof Nav || $item instanceof Form || $this->rules === 'all') {
            return $this->cacher->flush();
        }

        if ($item instanceof Entry && $url = $this->getLinkedModelUrl($item)) {
            $this->cacher->invalidateUrls([$url]);
            return;
        }

        parent::invalidate($item);
    }

    // Product and category entries have no route, their page lives on the url of the linked Magento model.
    protected function getLinkedModelUrl(Entry $entry): ?string
    {
        $field = match ($entry->collectionHandle()) {
            'products' => 'linked_product',
            'categories' => 'linked_category',
            default => null,
        };

        if (! $field || ! $value = Arr::first(Arr::wrap($entry->value($field)))) {
            return null;
        }

        // This could run in a queue, so we explicitly use the store of the entry its site.
        $url = Rapidez::withStore(
            $entry->site()->attributes()['magento_store_id'] ?? config('rapidez.store'),
            function () use ($field, $value) {
                if ($field === 'linked_category') {
                    return config('rapidez.models.category')::find($value)?->url;
                }

                $productModel = config('rapidez.models.product');

                return $productModel::selectAttributes(['url_key'])
                    ->where((new $productModel)->qualifyColumn('sku'), $value)
                    ->first()?->url;
            }
        );

        return $url ? URL::tidy($entry->site()->absoluteUrl() . '/' . $url) : null;
    }
}
