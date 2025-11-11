<?php

namespace Rapidez\Statamic\Models\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;

trait HasContentEntry
{
    protected function entry(): Attribute
    {
        if (!app()->runningInConsole()) {
            return Attribute::make(
                get: fn() => Entry::query()
                    ->where('collection', $this->collection)
                    ->where('site', Site::current()->handle())
                    ->where($this->linkField, $this?->{$this->linkKey ?? $this->getKeyName()} ?? null)
                    ->first(),
            )->shouldCache();    
        }
        
        return Attribute::make();
    }
}
