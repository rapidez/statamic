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
];
