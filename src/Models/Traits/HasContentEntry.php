<?php

namespace Rapidez\Statamic\Models\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Rapidez\Statamic\Models\BaseEntry;

trait HasContentEntry
{
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
                    ->where('site', $this->getSiteHandleByStoreId())
                    ->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(`statamic_entries`.`data`, "$.'.$this->linkField.'")) AS relation_id')
                    ->addSelect('id'),
                'subquery',
                'statamic_entries.id', '=', 'subquery.id'
            )
            ->withoutGlobalScopes();
    }

    public function getSiteHandleByStoreId(): string
    {
        $site = Site::all()
            ->filter(fn($_site) => ($_site?->attributes()['magento_store_id'] ?? null) == config('rapidez.store'))
            ->first();

        return $site?->handle() ?? config('rapidez.store_code');
    }

    public function throwMissingAttributeExceptionIfApplicable($key)
    {
        if ($this->relationLoaded('entry') && $this->entry) {
            return $this->entry?->data[$key] ?? null;
        }

        return parent::throwMissingAttributeExceptionIfApplicable($key);
    }
}
