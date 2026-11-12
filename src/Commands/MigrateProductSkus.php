<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
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

    protected array $resources = [];

    protected array $entityIds = [];

    protected array $unknown = [];

    protected int $converted = 0;

    protected int $changed = 0;

    public function handle(): int
    {
        $this->resources = Runway::allResources()
            ->filter(fn ($resource) => $resource->model() instanceof Product)
            ->map->handle()
            ->values()
            ->all();

        if (! $this->resources) {
            $this->error('No Runway resource found for ' . Product::class);

            return static::FAILURE;
        }

        $product = new Product;
        $this->entityIds = Product::withoutGlobalScopes()->pluck('entity_id', 'sku');

        foreach (Entry::query()->lazy() as $entry) {
            $this->migrate(
                $entry->blueprint()->fields()->except($product->linkField),
                $entry,
                'Entry ' . $entry->id() . ' (' . $entry->collectionHandle() . '/' . $entry->locale() . ')',
            );
        }

        foreach (GlobalSet::all() as $globalSet) {
            foreach ($globalSet->localizations() as $variables) {
                $this->migrate(
                    $variables->blueprint()->fields(),
                    $variables,
                    'Global ' . $globalSet->handle() . ' (' . $variables->locale() . ')',
                );
            }
        }

        $this->info(($this->option('dry-run') ? 'Would convert ' : 'Converted ') . $this->converted . ' SKUs in ' . $this->changed . ' items.');

        if ($this->unknown) {
            $this->warn('These string values are not a known SKU and were not changed: ' . implode(', ', array_keys($this->unknown)));
        }

        return static::SUCCESS;
    }

    protected function migrate(Fields $fields, $item, string $label): void
    {
        $original = $item->data()->all();
        $data = $this->convertFields($fields, $original);

        if ($data === $original) {
            return;
        }

        $this->changed++;
        $this->line($label);

        if ($this->option('dry-run')) {
            return;
        }

        $item->data($data)->saveQuietly();
    }

    protected function convertFields(Fields $fields, array $data): array
    {
        foreach ($fields->all() as $handle => $field) {
            if (array_key_exists($handle, $data)) {
                $data[$handle] = $this->convertField($field, $data[$handle]);
            }
        }

        return $data;
    }

    protected function convertField(Field $field, mixed $value): mixed
    {
        $fieldtype = $field->fieldtype();

        return match (true) {
            $fieldtype instanceof BaseFieldtype => in_array($field->get('resource'), $this->resources, true)
                ? $this->convertValue($value)
                : $value,
            $fieldtype instanceof Bard => $this->convertBard($fieldtype, $value),
            $fieldtype instanceof Replicator => $this->convertReplicator($fieldtype, $value),
            $fieldtype instanceof Grid => $this->convertGrid($fieldtype, $value),
            $fieldtype instanceof Group => is_array($value) ? $this->convertFields($fieldtype->fields(), $value) : $value,
            default => $value,
        };
    }

    protected function convertValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map($this->convertSku(...), $value);
        }

        return $this->convertSku($value);
    }

    protected function convertSku(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if (! isset($this->entityIds[$value])) {
            $this->unknown[$value] = true;

            return $value;
        }

        $this->converted++;

        return (int) $this->entityIds[$value];
    }

    protected function convertReplicator(Replicator $fieldtype, mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $index => $set) {
            if (is_array($set) && is_string($set['type'] ?? null)) {
                $value[$index] = $this->convertFields($fieldtype->fields($set['type'], $index), $set);
            }
        }

        return $value;
    }

    protected function convertBard(Bard $fieldtype, mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $index => $node) {
            $values = $node['attrs']['values'] ?? null;

            if (($node['type'] ?? null) === 'set' && is_array($values) && is_string($values['type'] ?? null)) {
                $value[$index]['attrs']['values'] = $this->convertFields($fieldtype->fields($values['type'], $index), $values);
            }
        }

        return $value;
    }

    protected function convertGrid(Grid $fieldtype, mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $index => $row) {
            if (is_array($row)) {
                $value[$index] = $this->convertFields($fieldtype->fields($index), $row);
            }
        }

        return $value;
    }
}
