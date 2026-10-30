<?php

namespace Rapidez\Statamic\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Rapidez\Statamic\Models\BaseEntry;
use Statamic\Facades\Site;
use Statamic\Statamic;

trait HasContentEntry
{
    public static function bootHasContentEntry(): void
    {
        static::addGlobalScope('entry', fn (Builder $builder) => $builder->with('entry'));
    }

    public function newQueryWithoutScopes()
    {
        return parent::newQueryWithoutScopes()
            ->afterQuery(function ($models) {
                $first = $models->first();

                if (! $first instanceof self) {
                    return;
                }

                $fieldsOnRunwayResource = $first
                    ->runwayResource()
                    ->blueprint()
                    ->fields()
                    ->all()
                    ->filter(fn ($option, $key) =>
                        $option->visibility() !== 'read_only' && $first->getKeyName() !== $key
                    )
                    ->keys();

                $models->each(function ($model) use ($fieldsOnRunwayResource) {
                    $entryData = $model->entry->data ?? [];
                    $filteredFields = $fieldsOnRunwayResource
                        ->filter(fn ($key) => boolval($entryData[$key] ?? null))->all();

                    $model->setRawAttributes(Arr::except(
                        $model->getAttributes(),
                        $filteredFields,
                    ));
                });
            });
    }

    public function entry(): BelongsTo
    {
        return $this
            ->belongsTo(
                BaseEntry::class,
                $this->linkKey ?? $this->getKeyName(),
                'subquery.relation_id',
            )
            ->joinSub(
                DB::table('statamic_entries')
                    ->where('site', $this->getSiteHandle())
                    ->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(`statamic_entries`.`data`, "$.'.$this->linkField.'")) AS relation_id')
                    ->addSelect('id'),
                'subquery',
                'statamic_entries.id', '=', 'subquery.id'
            )
            ->withoutGlobalScopes();
    }

    public function getSiteHandle(): string
    {
        if (Statamic::isCpRoute()) {
            return Site::selected()->handle();
        }

        $site = Site::all()
            ->filter(fn($site) => ($site?->attributes()['magento_store_id'] ?? null) == config('rapidez.store'))
            ->first();

        return $site?->handle() ?? config('rapidez.store_code');
    }

    public function throwMissingAttributeExceptionIfApplicable($key)
    {
        if ($this->relationLoaded('entry') && $this->entry && array_key_exists($key, $this->entry->data ?? [])) {
            return $this->entry->data[$key];
        }

        return parent::throwMissingAttributeExceptionIfApplicable($key);
    }
}
