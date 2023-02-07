<?php

namespace Rapidez\Statamic\Actions\Categories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Rapidez\Statamic\Actions\Traits\UpdateOrCreateEntry;
use Statamic\Facades\Entry;

class CreateCategories
{
    use UpdateOrCreateEntry;

    public function create(Collection $categories, string $storeCode): \Illuminate\Support\Collection
    {
        if ($categories->isEmpty()) {
            return new Collection();
        }

        return $categories->filter(fn ($category) => !blank($category->name) && !blank($category->url_path) && !blank($category->entity_id))->map(fn ($category) => [
            'magento_entity_id' => $category->entity_id,
            'magento_parent_id' => $category->parent_id ?? '',
            'title' => $category->name,
            'slug' => Str::replace('/', '-', $category->url_path),
            'url_path' => $category->url_path ? '/' . $category->url_path : '',
        ])->each(fn ($category) => self::updateOrCreate($category, 'categories', 'slug', $storeCode));
    }
}
