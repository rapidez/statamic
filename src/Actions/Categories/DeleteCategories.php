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

        $deletedCategories = Entry::whereCollection('categories')->where('locale', $storeCode)->filter(fn ($deletedCategory) => !$categorySlugs->contains($deletedCategory->get('slug')));
        $deletedCategories->each(fn ($deletedCategory) => $deletedCategory->delete());
    }
}
