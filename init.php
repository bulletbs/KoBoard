<?php defined('SYSPATH') or die('No direct script access.');


if(!Route::cache()){
    $board_base_url = '';
    Route::set('board', $board_base_url . '(<action>(/<id>)(/p<page>))', array('action' => '(all|most|main)', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'board',
            'action' => 'main',
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

    Route::set('board_city', $board_base_url . '<city_alias>(/p<page>).html', array('city_alias' => '[\w\-_]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'board',
            'action' => 'search',
            'city_alias' => 'all',
        ));

    Route::set('board_cat', $board_base_url . '<city_alias>(/<cat_alias>)(/p<page>).html', array('cat_alias' => '[\d\w\-_]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'board',
            'action' => 'search',
            'city_alias' => 'all',
        ));

    Route::set('board_myads', 'profile/board(/<action>(/<id>)(/p<page>.html))', array('action' => '(list|edit|enable|remove)', 'id' => '[0-9]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'userBoard',
            'action' => 'list',
        ));

    Route::set('board_search_widget', $board_base_url . 'boardSearch/<action>')
        ->defaults(array(
            'directory' => 'widgets',
            'controller' => 'BoardSearch',
            'action' => 'cities',
        ));

}