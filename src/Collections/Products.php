<?php

namespace Rapidez\Statamic\Collections;

class Products extends Base
{
    public static function handle(): string
    {
        return 'products';
    }

    public function title(): string
    {
        return __('Products');
    }
}