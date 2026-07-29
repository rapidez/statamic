<?php

namespace Rapidez\Statamic\Extend\Link;

use StatamicRadPack\Runway\ResourceLinkType;

/**
 * Runway only registers resources with frontend routing for the Link field.
 * Magento products/categories already have storefront URLs, so we register
 * them ourselves and resolve to Magento paths instead of runway_uris.
 */
class MagentoRunwayLinkType extends ResourceLinkType
{
    public function resolve(string $id, $parent = null, bool $localize = false): mixed
    {
        $model = parent::resolve($id, $parent, $localize);

        return $model ? new MagentoModelLink($model) : null;
    }
}
