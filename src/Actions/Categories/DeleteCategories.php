<?php

namespace Rapidez\Statamic\Actions\Categories;

use Illuminate\Support\Collection;
use Statamic\Facades\Entry;

class DeleteCategories
{
    public function deleteOldCategories(Collection $categorySlugs, ?string $storeCode): void
    {
        if ($categorySlugs->isEmpty()) {
            return;
        }

        $deletedCategories = Entry::whereCollection('categories')->where('locale', $storeCode)->filter(function ($deletedCategory) use ($categorySlugs, $storeCode) {
            return !$categorySlugs->contains($deletedCategory->get('slug'));
        });

        $deletedCategories->each(function ($deletedCategory) {
            $deletedCategory->delete();
        });
    }
}
