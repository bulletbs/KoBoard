<?php defined('SYSPATH') or die('No direct script access.');
return array(
    array(
        'name' => 'board',
        'priority' => '1.0',
        'frequency' => 'daily',
        'sources' =>array(
            // MAIN PAGE
            array(
                'return' => 'links', // returns links to sitemap files
                'links'=>array(
                    '',
                ),
                'priority' => '1.0',
                'frequency' => 'daily',
            ),
            // CONTENT PAGES
            array(
                'return' => 'links', // returns links to sitemap files
                'links'=>array(
                    'add',
                    'page/help',
                    'page/terms',
                    'contact',
                    'regions',
                    'categories',
                ),
                'priority' => '1.0',
                'frequency' => 'weekly',
            ),
            // BOARD PAGES
            array(
                'file' => 'categories',
                'model' => 'BoardCategory',
                'priority' => '0.9',
                'frequency' => 'weekly',
                'get_links_method' => 'sitemapCategories',
            ),
            array(
                'file' => 'regions',
                'model' => 'BoardCity',
                'priority' => '0.8',
                'frequency' => 'weekly',
                'get_links_method' => 'sitemapRegions',
            ),
            array(
                'model' => 'BoardAd',
                'get_links_method' => 'sitemapAds',
                'return' => 'sitemaps', // returns links to sitemap files
                'partable' => 10000,    // links per file
                'priority' => '0.4',
                'frequency' => 'daily',
            ),
        ),
    ),
);
