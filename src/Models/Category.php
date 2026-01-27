<?php

namespace Rapidez\Statamic\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Rapidez\Core\Models\Category as CoreCategory;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Rapidez\Statamic\Models\Traits\HasContentEntry;
use Rapidez\Statamic\Observers\RunwayObserver;

#[ObservedBy([RunwayObserver::class])]
class Category extends CoreCategory
{
    use HasRunwayResource, HasContentEntry;

    protected $with = ['entry'];

    public string $linkField = 'linked_category';
    public string $collection = 'categories';
}
