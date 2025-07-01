<?php

namespace Rapidez\Statamic\Observers;

use Illuminate\Database\Eloquent\Model;
use StatamicRadPack\Runway\Support\Json;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;

class RunwayObserver
{
    public function updating(Model $model)
    {
        $entry = $model->entry;

        if (!$entry) {
            $entry = Entry::make()
                ->collection($model->collection)
                ->locale(Site::selected()->handle())
                ->data([$model->linkField => $model->{$model->getKeyName()}]);
        }
        
        $attributes = $model->getDirty();
        
        foreach ($attributes as $key => $value) {
            // Is there a way we can detect this earlier?
            // So we know what's coming from for example
            // the blueprint as it's defined there.
            if (Json::isJson($value)) {
                $attributes[$key] = json_decode((string) $value, true);
            }
        }

        $entry->merge($attributes);

        // When we save via the facade we can bypass the auto generation 
        // of title and slug, we want this because there's no blueprint 
        // for the collection. We don't need this for now.
        Entry::save($entry);

        return false;
    }

    public function retrieved(Model $model)
    {
        $originalAttributes = $model->getAttributes();
        
        if ($model->exists && $model->entry) {
            $model->setRawAttributes(array_merge(
                $model->entry->data()->toArray(),
                $originalAttributes
            ));
        }
    }

    // Just to make sure you can't create or delete
    public function creating(Model $model)
    {
        return false;
    }

    public function deleting(Model $model)
    {
        return false;
    }
}
