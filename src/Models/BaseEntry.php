<?php

namespace Rapidez\Statamic\Models;

use Illuminate\Database\Eloquent\Builder;
use Rapidez\Core\Models\Model;
use Rapidez\Core\Models\Traits\Searchable;
use Statamic\Eloquent\Entries\Entry;
use Statamic\Facades\Site;
use TorMorten\Eventy\Facades\Eventy;

class BaseEntry extends Model
{
    use Searchable;

    protected $table = 'statamic_entries';
    protected static $collection;

    protected $casts = [
        'data' => 'json',
        'date' => 'date',
    ];

    protected static function booting()
    {
        static::addGlobalScope('collection', function (Builder $builder) {
            $builder->where('collection', static::$collection);
        });

        static::addGlobalScope('onlyCurrentSite', function (Builder $builder) {
            $site = Site::findByMageRunCode(config('rapidez.store_code'));
            $builder->where('site', $site);
        });
    }

    public static function getModelName(): string
    {
        return 'statamic.' . static::$collection;
    }

    public static function getIndexName(): string
    {
        return 'statamic.' . static::$collection;
    }

    public function toSearchableArray(): array
    {
        $entry = Entry::fromModel($this);

        $data = [
            'title' => $entry->title,
            'url' => $entry->url,
            'slug' => $entry->slug,
            'date' => $entry->date,
        ];

        return Eventy::filter('index.' . static::getModelName() . '.data', array_merge($data, $this->getAdditionalIndexData($entry)), $entry);
    }

    protected function getAdditionalIndexData(Entry $entry): array
    {
        return [];
    }

    protected function throwMissingAttributeExceptionIfApplicable($key)
    {
        if ($key == 'subquery.relation_id') {
            return $this->relation_id;
        }
    }
}
