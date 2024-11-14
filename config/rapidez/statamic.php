<?php

return [
    // Enable the routes? All Statamic routes will be enabled
    // as fallback so first Magento routes will be used.
    // Make sure you disabled the Statamic routing in
    // the "config/statamic/routes.php" file!
    'routes' => true,

    // Should the data from Statamic be fetched? The entry
    // data will be available as $content witin views.
    'fetch' => [
        // Product SKU will be matched with the "linked_product" SKU.
        'product' => true,

        // Category ID will be matched with the "linked_category" ID.
        'category' => true,
    ],

    // Which collection and blueprint should be used for importing products?
    'import' => [
        'categories' => [
            'collection' => 'categories',
            'blueprint' => 'category',
        ],
        'products' => [
            'collection' => 'products',
            'blueprint' => 'product',
        ],
        'brands' => [
            'collection' => 'brands',
            'blueprint' => 'brand',
        ],
    ],

    // This defines the multiplier for the responsive images and which size to show.
    // Default is 1.5x the displayed size.
    'responsive_image_multiplier' => 150,

    'runway' => [
        // Should we configure Runway? You'll get a products,
        // categories and brands / manufacturers resource.
        'configure' => true,

        // The attribute id used for the brand resource.
        'brand_attribute_id' => 83,

        // Only show these product visibilities.
        // VISIBILITY_NOT_VISIBLE    = 1;
        // VISIBILITY_IN_CATALOG     = 2;
        // VISIBILITY_IN_SEARCH      = 3;
        // VISIBILITY_BOTH           = 4;
        'product_visibility' => [2, 3, 4],

        // The Runway resources we'll configure.
        'resources' => [
            \Rapidez\Statamic\Models\Product::class => [
                'name' => 'Products',
                'read_only' => true,
                'title_field' => 'name',
                'cp_icon' => 'table',
            ],

            \Rapidez\Statamic\Models\Category::class => [
                'name' => 'Categories',
                'read_only' => true,
                'title_field' => 'name',
                'cp_icon' => 'array',
            ],

            \Rapidez\Statamic\Models\Brand::class => [
                'name' => 'Brands',
                'read_only' => true,
                'title_field' => 'value_store',
                'cp_icon' => 'tags',
                'order_by' => 'sort_order',
            ],
        ],
    ],

    'navigation' => [
        // When a menu get's created, these collections will
        // automaticly be added to the navigations allowed entries.
        // This way, we don't need to update every navigation with
        // settings and it will always be right.
        'allowed_collections' => [
            'pages',
            'brand',
            'category',
            'product',
        ]
    ],

    'disabled_sites' => [
        // '{site_handle}'
    ],

    'sitemap' => [
        'prefix' => env('STATAMIC_SITEMAP_PREFIX, 'statamic_sitemap_'),
        'storage_directory' => 'statamic-sitemaps/'
    ]
];
