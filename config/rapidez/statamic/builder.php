<?php

use Rapidez\Statamic\Collections\Brands;
use Rapidez\Statamic\Collections\Categories;
use Rapidez\Statamic\Collections\Products;

return [
    'collections' => [
        Products::class,
        Categories::class,
        Brands::class,
    ],
    'routes' => [
        Brands::class => [
            'default' => '{slug}'
        ],
    ]
];