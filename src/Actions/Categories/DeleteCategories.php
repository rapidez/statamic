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

        Entry::query()
            ->where('collection', 'categories')
            ->where('site', $storeCode)
            ->whereNotIn('slug', $categorySlugs)
            ->delete();
    }
}
