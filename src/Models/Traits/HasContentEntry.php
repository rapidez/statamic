<?php

namespace Rapidez\Statamic\Models\Traits;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Rapidez\Statamic\Observers\RunwayObserver;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;

trait HasContentEntry
{
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
        if (!app()->runningInConsole()) {
            return Attribute::make(
                get: fn() => Entry::query()
                    ->where('collection', $this->collection)
                    ->where('site', Site::selected()->handle())
                    ->where($this->linkField, $this->{$this->getKeyName()})
                    ->first(),
            )->shouldCache();    
        }
        
        return Attribute::make();
    }
}
