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

    // It can occur that you're using a Hybrid Runway resource,
    // but you want to have routing for that Hybrid Runway resource.
    // You can enable the routing by specifying the routes here, they will automaticly
    // be added to your Hybrid Runway resource.
    // For example:
    'routes' => [
        Brands::class => [
            'default' => '{slug}'
        ],
    ]
];