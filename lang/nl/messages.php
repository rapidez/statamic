<?php

return [
    'indexer' => [
        'title' => 'Indexer',
        'description' => 'Herbouw de Rapidez index.',
        'run' => 'Indexer starten',
        'last_run' => 'Laatste run',
        'never_run' => 'Er is nog geen indexrun geregistreerd.',
        'types' => 'Types',
        'types_placeholder' => 'Alle types',
        'stores' => 'Stores',
        'stores_placeholder' => 'Alle stores',
        'confirm' => [
            'title' => 'Indexer starten',
            'body' => 'Beperk de herindex optioneel tot specifieke types of stores. Laat leeg om alles te indexeren.',
            'button' => 'Indexer starten',
        ],
        'success' => [
            'queued' => 'Indexer gestart.',
            'completed' => 'Indexer voltooid.',
        ],
        'error' => 'Er ging iets mis tijdens het starten van de indexer.',
    ],
    'permission_run_indexer' => 'Rapidez-indexer uitvoeren',
];
