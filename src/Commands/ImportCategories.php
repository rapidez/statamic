<?php

namespace Rapidez\Statamic\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Rapidez\Core\Facades\Rapidez;
use Rapidez\Statamic\Jobs\ImportCategoriesJob;
use ReflectionClass;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Symfony\Component\Console\Helper\ProgressBar;

class ImportCategories extends Command
{
    protected $signature = 'rapidez:statamic:import:categories {categories?* : Categories in a space separated list, by entity_id or url_key} {--all : Process all categories if none have been passed} {--site=* : Sites handles or urls to process, if none are passed it will be done for all sites (Default will be all sites)}';

    protected $description = 'Create a new category entry for a category if it does not exist.';

    public function handle(): int
    {
        $categoryModel = config('rapidez.models.category');
        $categoryModelInstance = new $categoryModel;

        $categoryIdentifiers = $this->argument('categories');
        if (!$categoryIdentifiers && !$this->option('all')) {
            $this->error(__('You must enter categories or pass the --all flag.'));

            return static::FAILURE;
        }

        $sites = $this->option('site');
        $sites = $sites
            ? array_filter(array_map(fn($handle) => (Site::get($handle) ?: Site::findByUrl($handle)) ?: $this->output->warning(__('No site found with handle or url: :handle', ['handle' => $handle])), $sites))
            : Site::all();

        $bar = $this->output->createProgressBar(count($sites));
        $bar->start();
        foreach($sites as $site) {
            $bar->display();

            $siteAttributes = $site->attributes();
            if (!isset($siteAttributes['magento_store_id'])) {
                continue;
            }

            Rapidez::setStore($siteAttributes['magento_store_id']);

            $categories = $categoryModel::query()
                ->unless($categoryIdentifiers, fn ($query) => $query
                    ->whereNotNull('url_key')
                    ->whereNot('url_key', 'default-category')
                    ->where('product_count', '>', 0)
                )
                ->when($categoryIdentifiers, fn ($query) => $query
                    ->whereIn($categoryModelInstance->getQualifiedKeyName(), $categoryIdentifiers)
                    ->orWhereIn('url_key', $categoryIdentifiers)
                )
                ->lazy();

            foreach ($categories as $category) {
                static::createEntry(
                    [
                        'collection' => 'categories',
                        'blueprint'  => 'category',
                        'site'       => $site->handle(),
                        'linked_category' => $category->entity_id,
                    ],
                    array_merge(...Event::dispatch('rapidez-statamic:category-entry-data', ['category' => $category]))
                );
            }

            $bar->advance();
        }
        $bar->finish();


        return static::SUCCESS;
    }

    protected static function createEntry(array $attributes, array $values = [])
    {
        if (Entry::query()->where($attributes)->count()) {
            // Entry was already created.
            return;
        }

        /** @var \Statamic\Entries\Entry $entry */
        $entry = Entry::make();
        $values = array_merge($attributes, $values);

        static::setEntryData($entry, $values)->save();
    }

    protected static function setEntryData(\Statamic\Entries\Entry $entry, array $values = []) : \Statamic\Entries\Entry
    {
        $reflectedEntry = new ReflectionClass($entry);
        foreach ($values as $key => $value) {
            // Check if the key is a statamic setter
            if (!$reflectedEntry->hasMethod($key) || $reflectedEntry->getMethod($key)->getNumberOfParameters() < 1) {
                continue;
            }

            $entry->$key($value);
            unset($values[$key]);
        }

        $entry->merge($values);

        return $entry;
    }
}
