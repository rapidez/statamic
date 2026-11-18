<?php

namespace Rapidez\Statamic\Extend\Link;

use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Fields\Field;

/**
 * Brands link to their brand entry, so only offer them when that collection has a route.
 */
class BrandLinkType extends MagentoRunwayLinkType
{
    public function visible(Field $field): bool
    {
        $collection = CollectionFacade::findByHandle(config('rapidez.statamic.import.brands.collection', 'brands'));

        return (bool) $collection?->routes()->filter()->isNotEmpty();
    }
}
