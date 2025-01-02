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

            \Rapidez\Statamic\Models\ProductAttribute::class => [
                'name' => 'Product Attributes',
                'read_only' => true,
                'title_field' => 'frontend_label',
                'cp_icon' => 'tags',
                'listing' => [
                    'columns' => [
                        'attribute_code',
                        'frontend_label',
                        'frontend_input',
                        'is_filterable',
                        'is_searchable',
                        'formatted_options',
                    ],
                    'sort' => [
                        'field' => 'attribute_code',
                        'direction' => 'asc',
                    ],
                ],
                'search' => [
                    'fields' => [
                        'attribute_code',
                        'frontend_label',
                        'store_frontend_label',
                        'option_values',
                        'frontend_input',
                    ],
                ],
            ],

            \Rapidez\Statamic\Models\ProductAttributeOption::class => [
                'name' => 'Product Attribute Options',
                'read_only' => true,
                'title_field' => 'display_value',
                'cp_icon' => 'list-bullets',
                'listing' => [
                    'columns' => [
                        'option_id',
                        'attribute_code',
                        'attribute_label',
                        'admin_value',
                        'store_value',
                        'sort_order',
                    ],
                    'sort' => [
                        'field' => 'sort_order',
                        'direction' => 'asc',
                    ],
                ],
                'search' => [
                    'fields' => [
                        'option_id',
                        'attribute_code',
                        'attribute_label',
                        'admin_value',
                        'store_value',
                    ],
                ],
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
        'prefix' => env('STATAMIC_SITEMAP_PREFIX', 'statamic_sitemap_')
    ]
];
