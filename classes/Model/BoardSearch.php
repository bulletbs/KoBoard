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


}