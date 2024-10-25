<?php

return [
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
];
