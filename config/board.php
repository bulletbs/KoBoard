<?php defined('SYSPATH') or die('No direct script access');
return array(
    /* Module settings */
    'board_as_module'   => false, // set to TRUE, if Board is module of big portal
    'addnew_suspend'    => false, // set to TRUE to block adding ads

    'sphinx_index'      => 'sellmania_ads',

    /* Country settings */
    'country_name'     => 'Россия',
    'all_country'      => 'России',
    'in_country'       => 'в России',
    'h1_prefix'       => 'Все объявления в ',
    'breadcrumbs_prefix'       => 'Все объявления ',
    'show_ads_on_main'       => true,

    'price_units'       => array(
        'options' => array(
            'unit_0' => 'руб.',
            'unit_1' => '$',
            'unit_2' => '&euro;',
        ),
        'templates' => array(
            'unit_0' => '<price>&thinsp;руб.',
            'unit_1' => '$&thinsp;<price>',
            'unit_2' => '&euro;&thinsp;<price>',
        ),
        'iso' => array(
            'unit_0' => 'RUR',
            'unit_1' => 'USD',
            'unit_2' => 'EUR',
        ),
    ),
    'price_hints'       => '100,1000,5000,10000,20000,100000,1000000,10000000',

    /* H1 settings */
    'region_h1'           => 'Доска объявлений <region_of>',
    'category_h1'         => '<category> в России',
    'region_category_h1'  => '<category> в <region_in>',
    'query_h1'            => '<query>',
    'user_search_h1'      => 'Объявления пользователя <username>',
    'tags_h1'             => '<tag> в России',

    /* H2 settings */
    'region_h2'           => 'в <region_in>',
    'category_h2'         => '<category> в России',
    'region_category_h2'  => '<category> в <region_in>',
    'query_h2'            => 'по запросу <query>',

    /* TITLE settings */
    'region_title'           => 'Доска объявлений <region_of> &#8212; Объявления на сайте <project><page>',
    'category_title'         => '<category> в <region_of> на доске объявлений <project><page>',
    'region_category_title'  => '<category> в <region_of> на доске объявлений <project><page>',
    'ad_title'               => '<ad_title> &#8212; <category> в <city_in> на доске объявлений <project><page>',
    'tags_title'             => '<tag> в России',

    'add_title'              =>  'Подать бесплатное объявление на <project>',
    'region_map_title'       =>  'Карта реигонов и городов на <project>',
    'category_map_title'     =>  'Карта категорий на доске объявлений <project>',
    'query_title'            => '<query> - поиск на доске объявлений Doreno',
    'user_search_title'      => 'Объявления пользователя <username> на доске объявлений <project>',

    /* DESCRIPTION settings */
    'region_description'           => 'Бесплатные объявления <region_of>. Подайте бесплатные объявления о покупке и продаже на доске объявлений <project> вашего города',
    'category_description'         => 'Бесплатные объявления, <category>. Подайте бесплатные объявления о покупке и продаже на доске объявлений <project> вашего города',
    'region_category_description'  => 'Все объявления в категории <category>, <region>. Подайте бесплатные объявления о покупке и продаже в категорию <category>, <region> на доске объявлений <project>',
    'ad_description'               => '<ad_title>. <ad_descr>. Цена <ad_price> - <category> на доске объявлений <project>',
    'user_search_description'      => 'Все объявления, которые подал пользователь <username> на сайте бесплатных объявлений <project>',

    /* KEYWORDS settings */
    'ad_keywords'               => '<pcategory>, <category>',

    /* Breadcrumbs */
    'breadcrumbs_ad_title' => true,
    'breadcrumbs_category_title' => true,
    'breadcrumbs_region_title' => true,

    /* Images settings */
    'image_max_width'     => 800,
    'image_max_height'    => 450,
    'thumb_width'       => 140,
    'thumb_height'      => 105,

    /* AD page settings */
    'user_ads_show'        => true,
    'user_ads_limit'       => 5,
    'similars_ads_show'    => true,
    'similars_ads_limit'   => 8,
    'title_in_search'      => false,
    'ad_search_form'      => true,
    'ad_last_modify'      => false,
);
