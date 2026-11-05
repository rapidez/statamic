<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Statamic\Contracts\Structures\Nav;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Facades\Nav as NavFacade;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use StatamicRadPack\Runway\ResourceLinkType;

/**
 * Converts product, category and brand navigation items to a "link" field.
 *
 * A Runway field on the navigation item:
 *   ['title' => 'E-bikes', 'data' => ['category' => 1017]]
 *   => ['title' => 'E-bikes', 'data' => ['link' => 'runway_category::1017']]
 *
 * An entry from the old "product", "category" or "brand" collections,
 * where the entry has a Runway field like "linked_category" => 1017:
 *   ['entry' => '9b39...']
 *   => ['title' => 'E-bikes', 'data' => ['link' => 'runway_category::1017']]
 */
class MigrateNavLinks extends Command
{
    protected $signature = 'rapidez-statamic:migrate-nav-links
        {--nav=* : Only migrate these navigation handles}
        {--dry-run : Show what would be migrated without saving anything}';

    protected $description = 'Migrate product, category and brand navigation items to the link field';

    protected const RESOURCES = ['product', 'category', 'brand'];

    protected array $rows = [];

    public function handle(): int
    {
        foreach (NavFacade::all() as $nav) {
            if (! $this->option('nav') || in_array($nav->handle(), $this->option('nav'))) {
                $this->migrateNav($nav);
            }
        }

        $this->table(['Navigation', 'Site', 'Item', 'Link'], $this->rows);
        $this->info(($this->option('dry-run') ? 'Dry run, would migrate ' : 'Migrated ').count($this->rows).' navigation item(s).');

        return self::SUCCESS;
    }

    protected function migrateNav(Nav $nav): void
    {
        $fields = $this->resourceFields($nav->blueprint());
        $migrated = count($this->rows);

        foreach ($nav->trees() as $site => $tree) {
            $count = count($this->rows);
            $items = $this->migrateItems($tree->tree(), $fields, [$nav->handle(), $site]);

            if (! $this->option('dry-run') && count($this->rows) > $count) {
                $tree->tree($items)->save();
            }
        }

        if ($this->option('dry-run') || (! $fields && count($this->rows) === $migrated)) {
            return;
        }

        $blueprint = $nav->blueprint()->ensureField('link', ['type' => 'link', 'display' => 'Link', 'localizable' => true], prepend: true);

        foreach (array_keys($fields) as $field) {
            $blueprint->removeField($field);
        }

        $blueprint->save();
        $this->info("Updated the \"{$nav->handle()}\" navigation blueprint.");
    }

    protected function migrateItems(array $items, array $fields, array $row): array
    {
        foreach ($items as $index => $item) {
            if ($item['children'] ?? false) {
                $item['children'] = $this->migrateItems($item['children'], $fields, $row);
            }

            $link = $this->linkFromFields($item['data'] ?? [], $fields);
            $entry = $link ? null : EntryFacade::find($item['entry'] ?? null);

            if ($entry && in_array($entry->collectionHandle(), static::RESOURCES)) {
                $link = $this->linkFromFields($entry->data()->all(), $this->resourceFields($entry->blueprint()));

                if ($link) {
                    $item['title'] ??= $entry->title;
                    unset($item['entry']);
                }
            }

            if ($link) {
                $item['data'] = [...Arr::except($item['data'] ?? [], array_keys($fields)), 'link' => $link];
                $this->rows[] = [...$row, $item['title'] ?? $item['id'], $link];
            }

            $items[$index] = $item;
        }

        return $items;
    }

    protected function linkFromFields(array $data, array $fields): ?string
    {
        foreach ($fields as $field => $resource) {
            if ($id = Arr::first(Arr::wrap($data[$field] ?? null))) {
                return ResourceLinkType::PREFIX.$resource.'::'.$id;
            }
        }

        return null;
    }

    /**
     * The Runway fields linking to a product, category or brand, as handle => resource.
     */
    protected function resourceFields(Blueprint $blueprint): array
    {
        return $blueprint->fields()->all()
            ->filter(fn (Field $field) => in_array($field->get('resource'), static::RESOURCES))
            ->map(fn (Field $field) => $field->get('resource'))
            ->all();
    }
}
