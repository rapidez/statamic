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
    protected static $collection = 'pages';

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

    /** @return array<mixed> */
    public function toSearchableArray(): array
    {
        $entry = Entry::fromModel($this);

        /** @var array<mixed> $data */
        $data = Eventy::filter('index.' . static::getModelName() . '.data', $entry->toArray(), $entry);
        return $data;
    }
}
