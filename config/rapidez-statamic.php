<?php

return [
    // Enable the routes for the pages resource?
    'routes' => true,

    // Should the data from Statamic be fetched? The entry
    // data will be available as $content witin views.
    'fetch' => [
        // Product SKU will be matched with the "linked_product" SKU.
        'product' => true,

        // Category ID will be matched with the "linked_category" ID.
        'category' => true,
    ],

    'runway' => [
        // Should we configure Runway? You'll get a products,
        // categories and brands / manufacturers resource.
        'configure' => true,

        // The attribute id used for the brand resource.
        'brand_attribute_id' => 83,

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
