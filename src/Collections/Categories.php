<?php

namespace Rapidez\Statamic\Collections;

class Categories extends Base
{
    public static function handle(): string
    {
        return 'categories';
    }

    public function title(): string
    {
        return __('Categories');
    }

    public function visible(): bool
    {
        return false;
    }
}