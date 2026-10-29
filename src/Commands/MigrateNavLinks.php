<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Structures\Nav;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Facades\Nav as NavFacade;
use Statamic\Fields\Blueprint;
use Statamic\Structures\NavTree;
use StatamicRadPack\Runway\ResourceLinkType;
use StatamicRadPack\Runway\Runway;

class MigrateNavLinks extends Command
{
    protected $signature = 'rapidez-statamic:migrate-nav-links
        {--nav=* : Only migrate these navigation handles}
        {--field=link : The link field on the navigation items, use dot notation for a link field within a group}
        {--legacy-collection=* : The collections that were used to link products, categories and brands (default: product, category, brand)}
        {--keep-legacy-fields : Keep the product, category and brand fields on the navigation items}
        {--update-blueprints : Add the link field to and remove the legacy fields from the navigation blueprints}
        {--delete-legacy : Delete the migrated legacy entries and the legacy collections when they are empty}
        {--dry-run : Show what would be migrated without saving anything}';

    protected $description = 'Migrate product, category and brand navigation items to the link field';

    protected const RESOURCES = ['product', 'category', 'brand'];

    /** @var array<int, array<int, string>> */
    protected array $rows = [];

    /** @var array<string, Entry> */
    protected array $legacyEntries = [];

    public function handle(): int
    {
        $navs = NavFacade::all()->when(
            $this->option('nav'),
            fn (Collection $navs, array $handles) => $navs->filter(fn (Nav $nav) => in_array($nav->handle(), $handles))
        );

        foreach ($navs as $nav) {
            $this->migrateNav($nav);
        }

        if (! $this->rows) {
            $this->info('No navigation items to migrate.');

            return self::SUCCESS;
        }

        $this->table(['Navigation', 'Site', 'Item', 'Legacy', 'Link'], $this->rows);

        if ($this->option('dry-run')) {
            $this->warn('Dry run, nothing has been saved.');

            return self::SUCCESS;
        }

        if ($this->option('delete-legacy')) {
            $this->deleteLegacy();
        }

        $this->info('Migrated '.count($this->rows).' navigation item(s).');

        return self::SUCCESS;
    }

    protected function migrateNav(Nav $nav): void
    {
        $legacyFields = $this->legacyNavFields($nav->blueprint());
        $linkField = $this->option('field');

        foreach ($nav->trees() as $tree) {
            /** @var NavTree $tree */
            $count = count($this->rows);
            $items = $this->migrateItems($tree->tree(), $nav, $tree->locale(), $legacyFields);

            if (count($this->rows) > $count && ! $this->option('dry-run')) {
                $tree->tree($items)->save();
            }
        }

        $this->checkBlueprint($nav, $legacyFields, $linkField);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, string>  $legacyFields
     * @return array<int, array<string, mixed>>
     */
    protected function migrateItems(array $items, Nav $nav, string $site, array $legacyFields): array
    {
        foreach ($items as $index => $item) {
            if ($item['children'] ?? false) {
                $item['children'] = $this->migrateItems($item['children'], $nav, $site, $legacyFields);
            }

            $items[$index] = $this->migrateItem($item, $nav, $site, $legacyFields);
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, string>  $legacyFields
     * @return array<string, mixed>
     */
    protected function migrateItem(array $item, Nav $nav, string $site, array $legacyFields): array
    {
        $data = $item['data'] ?? [];
        $link = null;
        $legacy = null;

        // Runway relation fields directly on the navigation item.
        foreach ($legacyFields as $field => $resource) {
            if (! $link && $id = $this->relatedId($data[$field] ?? null)) {
                $link = ResourceLinkType::PREFIX.$resource.'::'.$id;
                $legacy = $field.': '.$id;
            }

            if (! $this->option('keep-legacy-fields')) {
                unset($data[$field]);
            }
        }

        // Navigation items linking to an entry within a legacy collection.
        if (! $link && isset($item['entry']) && $entry = EntryFacade::find($item['entry'])) {
            if (in_array($entry->collectionHandle(), $this->legacyCollections())) {
                [$link, $legacy] = $this->linkFromEntry($entry);

                if ($link) {
                    $this->legacyEntries[$entry->id()] = $entry;
                    $item['title'] ??= $entry->data()->get('title');
                    unset($item['entry']);
                }
            }
        }

        if (! $link) {
            return $item;
        }

        Arr::set($data, $this->option('field'), $link);
        $item['data'] = $data;

        $this->rows[] = [$nav->handle(), $site, $item['title'] ?? $item['id'], $legacy, $link];

        return $item;
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    protected function linkFromEntry(Entry $entry): array
    {
        $field = $entry->data()->keys()->first(
            fn (string $key) => str($key)->startsWith([$entry->collectionHandle(), 'linked_'])
        );

        if (! $field || ! $id = $this->relatedId($entry->data()->get($field))) {
            return [null, null];
        }

        $resource = $entry->blueprint()?->field($field)?->get('resource') ?? $entry->collectionHandle();

        if (! in_array($resource, static::RESOURCES) || ! Runway::hasResource($resource)) {
            return [null, null];
        }

        return [ResourceLinkType::PREFIX.$resource.'::'.$id, $entry->collectionHandle().' entry: '.$id];
    }

    protected function relatedId(mixed $value): ?string
    {
        $value = is_array($value) ? Arr::first($value) : $value;

        return is_scalar($value) && $value !== '' ? (string) $value : null;
    }

    /**
     * @return array<string, string> field handle => runway resource
     */
    protected function legacyNavFields(Blueprint $blueprint): array
    {
        return $blueprint->fields()->all()
            ->filter(fn ($field) => in_array($field->type(), ['has_many', 'belongs_to'])
                && in_array($field->get('resource'), static::RESOURCES))
            ->map(fn ($field) => $field->get('resource'))
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function legacyCollections(): array
    {
        return $this->option('legacy-collection') ?: static::RESOURCES;
    }

    /**
     * @param  array<string, string>  $legacyFields
     */
    protected function checkBlueprint(Nav $nav, array $legacyFields, string $linkField): void
    {
        $blueprint = $nav->blueprint();
        $handle = str($linkField)->before('.')->toString();
        $hasLinkField = $blueprint->hasField($handle);

        if ($hasLinkField && (! $legacyFields || $this->option('keep-legacy-fields'))) {
            return;
        }

        if (! $this->option('update-blueprints') || $this->option('dry-run')) {
            if (! $hasLinkField) {
                $this->warn("The \"{$nav->handle()}\" navigation blueprint has no \"{$handle}\" field.");
            }

            if ($legacyFields && ! $this->option('keep-legacy-fields')) {
                $this->warn("The \"{$nav->handle()}\" navigation blueprint still has the legacy field(s): ".implode(', ', array_keys($legacyFields)).'.');
            }

            return;
        }

        if (! $hasLinkField) {
            if ($handle !== $linkField) {
                $this->warn("Can't add the \"{$linkField}\" group field automatically, add it to the \"{$nav->handle()}\" navigation blueprint.");
            } else {
                $blueprint->ensureField($handle, ['type' => 'link', 'display' => 'Link', 'localizable' => true], prepend: true);
            }
        }

        if (! $this->option('keep-legacy-fields')) {
            foreach (array_keys($legacyFields) as $field) {
                $blueprint->removeField($field);
            }
        }

        $blueprint->save();
        $this->info("Updated the \"{$nav->handle()}\" navigation blueprint.");
    }

    protected function deleteLegacy(): void
    {
        foreach ($this->legacyEntries as $entry) {
            $entry->delete();
        }

        $this->info('Deleted '.count($this->legacyEntries).' legacy entries.');

        foreach ($this->legacyCollections() as $handle) {
            $collection = CollectionFacade::findByHandle($handle);

            if (! $collection || $collection->queryEntries()->count()) {
                continue;
            }

            if (! $this->confirm("The \"{$handle}\" collection is empty, do you want to delete it?")) {
                continue;
            }

            foreach (NavFacade::all() as $nav) {
                $nav->collections($nav->collections()->map->handle()->reject(fn ($collection) => $collection === $handle)->values()->all())->save();
            }

            $collection->delete();
        }
    }
}
