<?php

use Rapidez\Statamic\Pipelines\BardData\FixListItems;
use Rapidez\Statamic\Pipelines\Html\HtmlThroughMarkdown;
use Rapidez\Statamic\Pipelines\Html\TransformIframes;
use Rapidez\Statamic\Pipelines\Html\TransformRapidezContent;
use Rapidez\Statamic\Pipelines\Markdown\TransformImages;

return [
    /**
     * These are the pipelines your migrations will run through in order to format Magento data
     * into data Statamic can hold.
     */
    'pipelines' => [
        'clean_html' => [
            /**
             * These pipelines are executed by the CleanHtml action **in this order** 
             */
            'html' => [
                TransformRapidezContent::class,
                TransformIframes::class,
                HtmlThroughMarkdown::class,
            ],
            'markdown' => [
                TransformImages::class,
            ]
        ],
        'convert_field' => [
            FixListItems::class
        ]
    ]
];