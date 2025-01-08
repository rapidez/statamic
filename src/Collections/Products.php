<?php

namespace Rapidez\Statamic\Collections;

use Statamic\Facades\Site;
use Tdwesten\StatamicBuilder\BaseCollection;

class Products extends BaseCollection
{
    public static function handle(): string
    {
        return 'products';
    }

    public function title(): string
    {
        return __('Products');
    }

    public function route(): ?string
    {
        return null;
    }

    public function slugs(): bool
    {
        return true;
    }

    public function titleFormat(): null|string|array
    {
        return null;
    }

    public function mount(): ?string
    {
        return null;
    }

    public function date(): bool
    {
        return false;
    }

    public function sites(): array
    {
        return Site::all()->keys()->toArray();
    }

    public function template(): ?string
    {
        return null;
    }

    public function layout(): ?string
    {
        return null;
    }

    public function inject(): array
    {
        return [];
    }

    public function searchIndex(): string
    {
        return 'default';
    }

    public function revisionsEnabled(): bool
    {
        return false;
    }

    public function defaultPublishState(): bool
    {
        return true;
    }

    public function originBehavior(): string
    {
        return 'select';
    }

    public function structure(): ?array
    {
        return [
            'root' => false,
            'slugs' => false,
            'max_depth' => 1
        ];
    }

    public function sortBy(): ?string
    {
        return null;
    }

    public function sortDir(): ?string
    {
        return null;
    }

    public function taxonomies(): array
    {
        return [];
    }

    public function propagate(): ?bool
    {
        return null;
    }

    public function previewTargets(): array
    {
        return [];
    }

    public function autosave(): bool|int|null
    {
        return null;
    }

    public function futureDateBehavior(): ?string
    {
        return null;
    }
    public function pastDateBehavior(): ?string
    {
        return null;
    }
}