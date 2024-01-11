<?php

namespace Rapidez\Statamic\Commands;

use Statamic\Facades\Site;
use Illuminate\Console\Command;
use Rapidez\Core\Facades\Rapidez;
use Illuminate\Support\Facades\Event;
use Rapidez\Statamic\Actions\StatamicEntryAction;

class ImportCategories extends Command
{
    protected $signature = 'rapidez:statamic:import:categories {categories?* : Categories in a space separated list, by entity_id or url_key} {--all : Process all categories if none have been passed} {--site=* : Sites handles or urls to process, if none are passed it will be done for all sites (Default will be all sites)}';

    protected $description = 'Create a new category entry for a category if it does not exist.';

    public function handle(): int
    {
        $categoryModel = config('rapidez.models.category');
        $categoryModelInstance = new $categoryModel;

        /** @var StatamicEntryAction $statamicEntryAction */
        $statamicEntryAction = app(StatamicEntryAction::class);

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
                )
                ->when($categoryIdentifiers, fn ($query) => $query
                    ->whereIn($categoryModelInstance->getQualifiedKeyName(), $categoryIdentifiers)
                    ->orWhereIn('url_key', $categoryIdentifiers)
                )
                ->lazy();

            foreach ($categories as $category) {
                $statamicEntryAction::createEntry(
                    [
                        'collection' => config('rapidez-statamic.import.categories.collection', 'categories'),
                        'blueprint'  => config('rapidez-statamic.import.categories.blueprint', 'category'),
                        'site'       => $site->handle(),
                        'linked_category' => $category->entity_id,
                    ],
                    array_merge([
                        'locale'       => $site->handle(),
                        'site'       => $site->handle(),
                    ], ...array_filter(Event::dispatch('rapidez-statamic:category-entry-data', ['category' => $category])))
                );
            }

            $bar->advance();
        }
        $bar->finish();

        return static::SUCCESS;
    }
}
