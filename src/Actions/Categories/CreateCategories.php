<?php

namespace Rapidez\Statamic\Actions\Categories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;

class CreateCategories
{
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
            'path' => $category->url_path ?? '',
        ])->each(function ($category) use ($storeCode) {
            Entry::updateOrCreate($category, 'categories', 'slug', $storeCode);
        });
    }
}
