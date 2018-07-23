<?php defined('SYSPATH') or die('No direct script access');
return array(
    /* Module settings */
    'board_as_module'   => false, // set to TRUE, if Board is module of big portal
    'addnew_suspend'    => false, // set to TRUE to block adding ads

    'sphinx_index'      => 'sellmania_ads',

    /* Country settings */
    'country_name'     => 'Россия',
    'country_alias'     => 'all',
    'all_country'      => 'России',
    'in_country'       => 'в России',
    'h1_prefix'       => 'Все объявления в ',
    'breadcrumbs_prefix'       => 'Все объявления ',
    'show_ads_on_main'       => true, // выбор шаблона на главной (последние или карта)
    'allow_all_search'       => false, // разрешить поиск по стране (без параметров, все объявления)

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
    'ad_h1'             => '<ad_title> в <city_in>',
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

    /* NOT FOUND title */
    'region_empty'           => 'В <region_in> не найдено объявлений.<br> Вы можете стать первым кто подаст сюда объявление.',
    'category_empty'         => 'В категории <category> не найдено объявлений.<br> Вы можете стать первым кто подаст сюда объявление.',
    'region_category_empty'  => 'В категории <category> <region_of> не найдено объявлений.<br> Вы можете стать первым кто подаст сюда объявление.',
    'query_empty'            => 'По запросу <query> в <category> <region_of> объявления не найдены ',
    'user_search_empty'         => 'Объявления пользователя <username> не найдены',
    'tags_empty'                => 'Объявлений с тегом <tag> не найдено',

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
    'tags_description'             => 'Все объявления, с тегом <tag> на сайте бесплатных объявлений <project>',

    /* KEYWORDS settings */
    'ad_keywords'               => '<pcategory>, <category>',

    /* Breadcrumbs */
    'breadcrumbs_ad_title' => true, // выводить заголовок объявления в крошках (объявление)
    'breadcrumbs_category_title' => true, // выводить заголовок категории в крошках (поиск)
    'breadcrumbs_region_title' => true, // выводить заголовок региона в кошках (поиск)

    'breadcrumbs_ad_region_all' => true, // выводить все вложности регионов в крошках (объявление)
    'breadcrumbs_search_region_all' =>true, // выводить все вложности регионов в крошках (поиск)

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

    /* Redirects */
    'redirect_noad'         => false, // перенаправлять, если объявление не найдено (вместо 404)
    'redirect_ad_wrong_city'   => true, // перенаправлять объявление, если алиас города не совпадает
    'redirect_ad_wrong_cat'    => true, // перенаправлять объявление, если алиас категории не совпадает

    /* Mailer settings */
    'mailer_queue_step' => 3000,
);
