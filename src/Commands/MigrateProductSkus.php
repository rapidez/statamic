<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Rapidez\Statamic\Models\Product;
use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Fieldtypes\Bard;
use Statamic\Fieldtypes\Grid;
use Statamic\Fieldtypes\Group;
use Statamic\Fieldtypes\Replicator;
use StatamicRadPack\Runway\Fieldtypes\BaseFieldtype;
use StatamicRadPack\Runway\Runway;

class MigrateProductSkus extends Command
{
    protected $signature = 'rapidez-statamic:migrate-product-skus {--dry-run : Show what would change without saving}';

    protected $description = 'Replace product SKUs with entity ids in Runway product fields';

    protected Collection $resources;

    protected Collection $entityIds;

    protected Collection $unknown;

    protected int $converted = 0;

    protected int $changed = 0;

    public function handle(): int
    {
        $this->resources = Runway::allResources()
            ->filter(fn ($resource) => $resource->model() instanceof Product)
            ->map->handle();

        if ($this->resources->isEmpty()) {
            $this->error('No Runway resource found for ' . Product::class);

            return static::FAILURE;
        }

        $product = new Product;
        $this->entityIds = Product::withoutGlobalScopes()->pluck('entity_id', 'sku');
        $this->unknown = collect();

        foreach (Entry::query()->lazy() as $entry) {
            $this->migrate(
                $entry->blueprint()->fields()->except($product->linkField),
                $entry,
                'Entry ' . $entry->id() . ' (' . $entry->collectionHandle() . '/' . $entry->locale() . ')',
            );
        }

        foreach(GlobalSet::all() as $globalSet) {
            foreach ($globalSet->localizations() as $variables) {
                $this->migrate(
                    $variables->blueprint()->fields(),
                    $variables,
                    'Global ' . $globalSet->handle() . ' (' . $variables->locale() . ')',
                );
            }
        }

        $this->info(($this->option('dry-run') ? 'Would convert ' : 'Converted ') . $this->converted . ' SKUs in ' . $this->changed . ' items.');

        if ($this->unknown->isNotEmpty()) {
            $this->warn('These string values are not a known SKU and were not changed: ' . $this->unknown->unique()->implode(', '));
        }

        return static::SUCCESS;
    }

    protected function migrate(Fields $fields, $item, string $label): void
    {
        $converted = $this->converted;
        $data = $this->convertFields($fields, $item->data());

        if ($this->converted === $converted) {
            return;
        }

        $this->changed++;
        $this->line($label);

        if ($this->option('dry-run')) {
            return;
        }

        $item->data($data->toArray())->saveQuietly();
    }

    protected function convertFields(Fields $fields, Collection $data): Collection
    {
        return $data->map(fn ($value, $handle) => $fields->has($handle)
            ? $this->convertField($fields->get($handle), $value)
            : $value
        );
    }

    protected function convertField(Field $field, mixed $value): mixed
    {
        $fieldtype = $field->fieldtype();

        return match (true) {
            $fieldtype instanceof BaseFieldtype => $this->resources->contains($field->get('resource'))
                ? $this->convertValue($value)
                : $value,
            $fieldtype instanceof Bard => $this->convertBard($fieldtype, $value),
            $fieldtype instanceof Replicator => $this->convertReplicator($fieldtype, $value),
            $fieldtype instanceof Grid => $this->convertGrid($fieldtype, $value),
            $fieldtype instanceof Group => is_array($value) ? $this->convertFields($fieldtype->fields(), collect($value)) : $value,
            default => $value,
        };
    }

    protected function convertValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return collect($value)->map($this->convertSku(...));
        }

        return $this->convertSku($value);
    }

    protected function convertSku(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if (! $this->entityIds->has($value)) {
            $this->unknown->push($value);

            return $value;
        }

        $this->converted++;

        return (int) $this->entityIds->get($value);
    }

    protected function convertReplicator(Replicator $fieldtype, mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        return collect($value)->map(fn ($set, $index) => is_array($set) && is_string($set['type'] ?? null)
            ? $this->convertFields($fieldtype->fields($set['type'], $index), collect($set))
            : $set
        );
    }

    protected function convertBard(Bard $fieldtype, mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        return collect($value)->map(function ($node, $index) use ($fieldtype) {
            $values = $node['attrs']['values'] ?? null;

            if (($node['type'] ?? null) === 'set' && is_array($values) && is_string($values['type'] ?? null)) {
                $node['attrs']['values'] = $this->convertFields($fieldtype->fields($values['type'], $index), collect($values));
            }

            return $node;
        });
    }

    protected function convertGrid(Grid $fieldtype, mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        return collect($value)->map(fn ($row, $index) => is_array($row)
            ? $this->convertFields($fieldtype->fields($index), collect($row))
            : $row
        );
    }
}
