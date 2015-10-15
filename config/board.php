<?php defined('SYSPATH') or die('No direct script access');
return array(
    /* Module settings */
    'board_as_module'   => false, // set to TRUE, if Board is module of big portal
    'addnew_suspend'    => false, // set to TRUE to block adding ads

    /* Country settings */
    'country_name'     => 'Россия',
    'all_country'      => 'Вся Россия',
    'in_country'       => 'в России',
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
    ),
    'price_hints'       => '100,1000,5000,10000,20000,100000,1000000,10000000',

    /* TITLE settings */
    'region_title'           => '<region> - Бесплатные объявления на <project><page>',
    'category_title'         => '<category> на доске объявлений <project><page>',
    'region_category_title'  => '<category>, <region> на доске объявлений <project><page>',
    'ad_title'               => '<ad_title> - <category>',

    /* DESCRIPTION settings */
    'region_description'           => 'Бесплатные объявления, <region>. Подайте бесплатные объявления о покупке и продаже на сайте <project> в вашем городе.',
    'category_description'         => 'Бесплатные объявления, <category>. Подайте бесплатные объявления о покупке и продаже на сайте <project> в вашем городе.',
    'region_category_description'  => 'Бесплатные объявления, <category> <region>. Подайте бесплатные объявления о покупке и продаже на сайте <project> в вашем городе.',
    'ad_description'               => '<ad_description> - <category>',

    /* Images settings */
    'image_max_width'     => 800,
    'image_max_height'    => 450,
    'thumb_width'   => 100,
    'thumb_height'  => 75,

    /* AD page settings */
    'user_ads_limit'        => 4,
    'similars_ads_limit'    => 10,
);
