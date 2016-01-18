<?php defined('SYSPATH') or die('No direct script access.');
return array(
    array(
        'name' => 'board',
        'priority' => '0.5',
        'sources' =>array(
            array(
                'file' => 'caegories',
                'model' => 'BoardCategory',
                'get_links_method' => 'sitemapCategories',
            ),
            array(
                'file' => 'regions',
                'model' => 'BoardCity',
                'get_links_method' => 'sitemapRegions',
            ),
            array(
                'model' => 'BoardAd',
                'get_links_method' => 'sitemapAds',
                'return' => 'sitemaps', // returns links to sitemap files
                'partable' => 10000,    // links per file
            ),
        ),
        'links' => array(
            'add',
            'page/help',
            'page/terms',
            'contact',
            'regions',
            'categories',
        ),
    ),
);
