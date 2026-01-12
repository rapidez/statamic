<?php

namespace Rapidez\Statamic\Models\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Rapidez\Statamic\Models\QueryBuilder\EntryFieldSortableBuilder;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Statamic;

trait HasContentEntry
{
    public function newEloquentBuilder($query)
    {
        return new EntryFieldSortableBuilder($query);
    }

    protected function entry(): Attribute
    {
        if (!app()->runningInConsole()) {
            return Attribute::make(
                get: fn() => Entry::query()
                    ->where('collection', $this->collection)
                    ->where('site', Statamic::isCpRoute() ? Site::selected()->handle() : Site::current()->handle())
                    ->where($this->linkField, $this?->{$this->linkKey ?? $this->getKeyName()} ?? null)
                    ->first(),
            )->shouldCache();    
        }
        
        return Attribute::make();
    }
}
