<?php

namespace Rapidez\Statamic\Collections;

class Brands extends Base
{
    protected string $handle = 'brands';

    public static function handle(): string
    {
        return 'brands';
    }

    public function title(): string
    {
        return __('Brands');
    }

    public function structure(): ?array
    {
        return [
            'root' => false,
            'slugs' => true,
            'max_depth' => 1
        ];
    }

    public function template(): ?string
    {
        return 'rapidez-statamic::brands.show';
    }

    public function visible(): bool
    {
        return false;
    }
}
