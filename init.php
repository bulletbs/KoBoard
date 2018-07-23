<?php defined('SYSPATH') or die('No direct script access.');

if(!Route::cache()){
    if(!is_file(__DIR__ . '/base_uri.cfg'))
        throw new Exception('Cannot find file '.__DIR__ .'/base_uri.cfg');
    $board_base_url = file_get_contents(__DIR__ . '/base_uri.cfg');
    $_init_actions = array(
        'main',
        'add',
        'goto',
        'favorites',
    );
    Route::set('board', $board_base_url . '<action>(/<id>)(/p<page>)', array('action' => '('.implode('|', $_init_actions).')', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'board',
            'action' => 'main',
        ));
    Route::set('board_myads', 'my-ads(/<action>(/<id>)(/p<page>.html))', array('action' => '(list|edit|enable|remove|refresh|refresh_all|import)', 'id' => '[0-9]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'userBoard',
            'action' => 'list',
        ));

    Route::set('board_ad_confirm', $board_base_url . 'confirm/<id>-<key>', array( 'id' => '[0-9]+', 'key' => '[0-9a-f]+'))
        ->defaults(array(
            'controller' => 'board',
            'action' => 'confirm',
        ));

    Route::set('board_ad', $board_base_url . '<city_alias>/<cat_alias>/<id>-<alias>.html', array( 'city_alias' => '([\w\-_]+)', 'cat_alias' => '[\d\w\-_]+', 'id' => '[0-9]+', 'alias' => '[\d\w\-_]+'))
        ->defaults(array(
            'controller' => 'board',
            'action' => 'ad',
        ));

    Route::set('board_ad_old', $board_base_url . '<city_alias>/<cat_alias>/<alias>-<id>', array( 'city_alias' => '([\w\-_]+)', 'cat_alias' => '[\d\w\-_]+', 'id' => '[0-9]+', 'alias' => '[\d\w\-_]+'))
        ->defaults(array(
            'controller' => 'board',
            'action' => 'adold',
        ));
    Route::set('board_ad_print', $board_base_url . '<city_alias>/<cat_alias>/print-<id>-<alias>.html', array( 'city_alias' => '([\w\-_]+)', 'cat_alias' => '[\d\w\-_]+', 'id' => '[0-9]+', 'alias' => '[\d\w\-_]+'))
        ->defaults(array(
            'controller' => 'board',
            'action' => 'ad',
            'print' => true,
        ));
    Route::set('board_city', $board_base_url . '<city_alias>(/p<page>).html', array('city_alias' => '[a-z\-_]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'board',
            'action' => 'search',
            'city_alias' => 'all',
        ));
    Route::set('board_cat', $board_base_url . '<city_alias>(/<cat_alias>)(/p<page>).html', array('city_alias' => '[a-z\-_]+', 'cat_alias' => '[a-z\-_]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'board',
            'action' => 'search',
            'city_alias' => 'all',
        ));
    Route::set('board_subcat', $board_base_url . '<city_alias>(/<cat_alias>)(/<filter_alias>)(/p<page>).html', array('city_alias' => '[a-z\-_]+', 'cat_alias' => '[a-z\-_]+', 'subcat_alias' => '[\da-z\-_]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'board',
            'action' => 'search',
            'city_alias' => 'all',
        ));

    Route::set('board_regions', 'regions')
        ->defaults(array(
            'controller' => 'board',
            'action' => 'tree',
        ));
    Route::set('board_categories', 'categories')
        ->defaults(array(
            'controller' => 'board',
            'action' => 'categories',
        ));

    Route::set('board_userads', 'userslist/<user>(/p<page>)', array('id' => '[0-9]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'Board',
            'action' => 'search',
        ));

    Route::set('board_notices', 'notices(/<action>(/<id>)(/p<page>.html))', array('action' => '(notice_remove|notice_clean)', 'id' => '[0-9]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'userBoard',
            'action' => 'notices',
        ));

    Route::set('board_search_widget', $board_base_url . 'boardSearch/<action>(/<id>)')
        ->defaults(array(
            'directory' => 'widgets',
            'controller' => 'BoardSearch',
            'action' => 'cities',
        ));

    Route::set('board_old_ads', 'pagemoved/<id>', array('id' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'Board',
            'action' => 'pagemoved',
        ));

    Route::set('board_old_cats', 'pagemoved/c<cat_id>', array('cat_id' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'Board',
            'action' => 'pagemoved',
        ));

    Route::set('board_tag', 'tags/<cat_alias>/<tagid>(/p<page>)', array('id' => '[0-9]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'Board',
            'action' => 'tags',
        ));
    Route::set('board_term', 'term/<term>(/p<page>)', array('term' => '[\s\d\wА-Яа-я]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'Board',
            'action' => 'search',
        ));
    Route::set('board_autocomplete', 'autocomplete')
        ->defaults(array(
            'controller' => 'Board',
            'action' => 'autocomplete',
        ));
}