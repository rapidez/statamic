<?php

namespace Rapidez\Statamic\Actions\Categories;

use Rapidez\Statamic\Events\CategoriesImportedEvent;
use Statamic\Facades\Site;

class ImportCategories
{
    public function __construct(
        public CreateCategories $createCategories,
        public DeleteCategories $deleteCategories,
        protected int $chunkSize = 20
    ) {
    }

    public function import(): void
    {
        $categoryModel = config('rapidez.models.category');
        $categoryModelInstance = new $categoryModel;

        foreach(Site::all() as $site) {
            $siteAttributes = $site->attributes();

            if (!isset($siteAttributes['magento_store_id'])) {
                continue;
            }

            config()->set('rapidez.store', $siteAttributes['magento_store_id']);

            $categorySlugs = collect();
            $categoryQuery = $categoryModel::select($categoryModelInstance->qualifyColumns(['entity_id', 'parent_id', 'name', 'url_key', 'url_path']))
                ->whereNotNull('url_key')->whereNot('url_key', 'default-category')
                ->where('children_count', '>', 0);

            $categoryQuery->chunk($this->chunkSize, function ($categories) use ($site, &$categorySlugs) {
                $categories = $this->createCategories->create($categories, $site->handle());
                $categorySlugs = $categorySlugs->merge($categories->map(fn ($category) => $category['slug']));
            });

            $this->deleteCategories->deleteOldCategories($categorySlugs, $site->handle());
        }

        CategoriesImportedEvent::dispatch();
    }
}
