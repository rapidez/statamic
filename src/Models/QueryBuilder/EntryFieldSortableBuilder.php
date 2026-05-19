<?php

namespace Rapidez\Statamic\Models\QueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Statamic\Facades\Site;
use Statamic\Statamic;

class EntryFieldSortableBuilder extends Builder
{
    protected $entryFields = null;
    protected $tableColumns = null;
    protected $entriesTableJoined = false;

    public function orderBy($column, $direction = 'asc')
    {
        $model = $this->getModel();

        if (!method_exists($model, 'runwayResource') || !$model->runwayResource()) {
            return parent::orderBy($column, $direction);
        }

        if ($this->entryFields === null) {
            $blueprint = $model->runwayResource()->blueprint();
            $this->entryFields = $blueprint
                ->fields()
                ->all()
                ->filter(fn($field) => $field->visibility() !== 'read_only')
                ->keys()
                ->toArray();
        }

        if ($this->tableColumns === null) {
            $this->tableColumns = Schema::getColumnListing($model->getTable());
        }

        if (in_array($column, $this->entryFields) && !in_array($column, $this->tableColumns)) {
            if ($this->joinEntriesTableIfNeeded($model)) {
                $entriesTable = $this->getEntriesTableName();
                $jsonPath = "JSON_UNQUOTE(JSON_EXTRACT({$entriesTable}.data, '$.{$column}'))";
                
                return $this->orderByRaw("{$jsonPath} " . (strtolower($direction) === 'desc' ? 'desc' : 'asc'));
            }
        }

        return parent::orderBy($column, $direction);
    }

    protected function getEntriesTableName(): string
    {
        $tablePrefix = config('statamic.eloquent-driver.table_prefix', 'statamic_');
        return $tablePrefix . 'entries';
    }

    protected function joinEntriesTableIfNeeded($model): bool
    {
        if ($this->entriesTableJoined) {
            return true;
        }

        $entriesTable = $this->getEntriesTableName();
        $hasJoin = collect($this->getQuery()->joins ?? [])
            ->contains(fn($join) => $join->table === $entriesTable);

        if (!$hasJoin) {
            try {
                $siteHandle = Statamic::isCpRoute() 
                    ? Site::selected()->handle() 
                    : Site::current()->handle();
            } catch (\Exception $e) {
                return false;
            }
            
            $linkField = $model->linkField ?? 'linked_product';
            $linkKey = $model->linkKey ?? $model->getKeyName();
            $tableName = $model->getTable();
            $grammar = $this->getQuery()->getGrammar();

            $this->leftJoin($entriesTable, function ($join) use ($tableName, $linkField, $linkKey, $model, $siteHandle, $entriesTable, $grammar) {
                $dataColumnQuoted = $grammar->wrapTable($entriesTable) . '.' . $grammar->wrap('data');
                $linkFieldJsonPath = "JSON_UNQUOTE(JSON_EXTRACT({$dataColumnQuoted}, '$.{$linkField}'))";
                
                $join->whereRaw("{$linkFieldJsonPath} = {$grammar->wrapTable($tableName)}.{$grammar->wrap($linkKey)}")
                    ->where("{$entriesTable}.collection", '=', $model->collection)
                    ->where("{$entriesTable}.site", '=', $siteHandle);
            });

            $this->entriesTableJoined = true;
        }

        return true;
    }
}
