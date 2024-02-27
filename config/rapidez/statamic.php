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
                'blueprint' => [
                    'sections' => [
                        'main' => [
                            'fields' => [
                                ['handle' => 'entity_id', 'field' => ['type' => 'integer']],
                                ['handle' => 'sku', 'field' => ['type' => 'text']],
                                ['handle' => 'name', 'field' => ['type' => 'text']],
                            ]
                        ]
                    ]
                ]
            ],

            \Rapidez\Statamic\Models\Category::class => [
                'name' => 'Categories',
                'read_only' => true,
                'title_field' => 'name',
                'cp_icon' => 'array',
                'blueprint' => [
                    'sections' => [
                        'main' => [
                            'fields' => [
                                ['handle' => 'entity_id', 'field' => ['type' => 'integer']],
                                ['handle' => 'name', 'field' => ['type' => 'text']],
                            ]
                        ]
                    ]
                ]
            ],

            \Rapidez\Statamic\Models\Brand::class => [
                'name' => 'Brands',
                'read_only' => true,
                'title_field' => 'value_store',
                'cp_icon' => 'tags',
                'order_by' => 'sort_order',
                'blueprint' => [
                    'sections' => [
                        'main' => [
                            'fields' => [
                                ['handle' => 'option_id', 'field' => ['type' => 'integer']],
                                ['handle' => 'sort_order', 'field' => ['type' => 'integer']],
                                ['handle' => 'value_admin', 'field' => ['type' => 'text']],
                                ['handle' => 'value_store', 'field' => ['type' => 'text']],
                            ]
                        ]
                    ]
                ],
            ],
        ],
    ],
];
