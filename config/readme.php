<?php

declare(strict_types=1);

return [
    'docs' => [
        'route' => '/docs',
        'path' => resource_path('docs'),
        'landing' => 'index',
        'menu' => 'documentation',
        'middleware' => ['web'],
    ],
    'versions' => [
        'default' => 'v1',
        'published' => [
            'v1',
        ],
    ],
    'variables' => [],
    'cache_time' => 600,
    'blade_support' => true,
    'extensions' => [],
    // markdown configuration
    'markdown' => [
        'table_of_contents' => [
            'html_class' => 'table-of-contents',
            'position' => 'top',
            'style' => 'bullet',
            'min_heading_level' => 1,
            'max_heading_level' => 3,
            'normalize' => 'relative',
            'placeholder' => '<!--[TOC]-->',
        ],
        'heading_permalink' => [
            'html_class' => 'heading-permalink',
            'id_prefix' => 'content',
            'fragment_prefix' => 'content',
            'insert' => 'before',
            'min_heading_level' => 1,
            'max_heading_level' => 3,
            'title' => 'Permalink',
            'aria_hidden' => true,
            'symbol' => '',
        ],
    ],
];
