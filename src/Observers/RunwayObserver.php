<?php

namespace Rapidez\Statamic\Observers;

use Illuminate\Database\Eloquent\Model;
use Statamic\Eloquent\Entries\Entry as StatamicEntry;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Support\Arr;

class RunwayObserver
{
    public function updating(Model $model)
    {
        if (!$model->entry || !$entry = StatamicEntry::fromModel($model->entry)) {
            $entry = Entry::make()
                ->collection($model->collection)
                ->locale(Site::selected()->handle())
                ->data([$model->linkField => $model->{$model->linkKey ?? $model->getKeyName()}]);
        }

        $attributes = $model->getDirty();

        foreach ($attributes as $key => $value) {
            // Is there a way we can detect this earlier?
            // So we know what's coming from for example
            // the blueprint as it's defined there.
            if (is_string($value) && json_validate($value)) {
                $attributes[$key] = json_decode((string) $value, true);
            }
        }

        if ($attributes['slug'] ?? null) {
            $entry->slug($attributes['slug']);
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
        if (!$model->exists) {
            return;
        }

        $fieldsOnRunwayResource = $model
            ->runwayResource()
            ->blueprint()
            ->fields()
            ->all()
            // Filter all read_only variables because they should always come from magento
            ->filter(fn($option, $key) =>
                $option->visibility() !== 'read_only' && $model->getKeyName() !== $key
            )
            ->keys()
            ->toArray();

        // Exclude the potential duplicated keys
        // Ignore the Magento values in that case
        $filteredAttributes = Arr::except(
            $model->getAttributes(),
            $fieldsOnRunwayResource
        );

        $model->setRawAttributes($filteredAttributes);
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
