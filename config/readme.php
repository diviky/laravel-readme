<?php

declare(strict_types=1);

use Diviky\Readme\Helpers\AssetsParser;
use Diviky\Readme\Parsers\CodeParser;

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
    'cache_time' => 600,
    'blade_support' => true,
    // markdown configuration
    'markdown' => [
        'table_of_contents' => [
            'html_class' => 'table-of-contents',
            'position' => 'top',
            'style' => 'bullet',
            'min_heading_level' => 2,
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

    'default_parsers' => [
        'code' => CodeParser::class,
        'assets' => AssetsParser::class,
    ],
    'parsers' => [],
    'extensions' => [],
    'variables' => [],
    /*
     * Language should generate for code block using postman language generator
     * Should be postman getAvailableLanguages response format
     */
    'snippets' => null,

    // Primary Languages Should Generate with variants
    'code' => [
        'csharp',
        'curl',
        'go',
        'java',
        'javascript',
        'kotlin',
        'nodejs',
        'php',
        'python',
        'ruby',
        'swift',
    ],
];
