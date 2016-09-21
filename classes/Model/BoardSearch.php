<?php defined('SYSPATH') or die('No direct script access');

/**
 * Model of board search statistics
 * Class Model_BoardSearch
 */
class Model_BoardSearch extends ORM{

    protected $_table_name = 'ad_search';

    public function labels(){
        return array(
            'id'            => 'Id',
            'query'          => 'Запрос',
            'category_id'   => 'Категория',
            'cnt'           => 'Запросов',
        );
    }

    public function getUri(){
        return Route::get('board_tag')->uri(array(
            'tagid' => $this->id,
            'cat_alias' => $this->category_id ? Model_BoardCategory::getField('alias', $this->category_id) : 'all',
        ));
    }
}