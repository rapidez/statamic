<?php

return [
    'indexer' => [
        'title' => 'Indexer',
        'description' => 'Rebuild the Rapidez index.',
        'run' => 'Run indexer',
        'last_run' => 'Last run',
        'never_run' => 'No index run has been recorded yet.',
        'types' => 'Types',
        'types_placeholder' => 'All types',
        'stores' => 'Stores',
        'stores_placeholder' => 'All stores',
        'confirm' => [
            'title' => 'Run indexer',
            'body' => 'Optionally narrow the reindex to specific types or stores. Leave empty to index everything.',
            'button' => 'Start indexer',
        ],
        'success' => [
            'queued' => 'Indexer started.',
            'completed' => 'Indexer completed.',
        ],
        'error' => 'Something went wrong while running the indexer.',
    ],
    'permission_run_indexer' => 'Run Rapidez indexer',
];
