<?php defined('SYSPATH') or die('No direct script access');

/**
 * Model of board search statistics
 * Class Model_BoardSearch
 */
class Model_BoardSearch extends ORM{

    protected $_table_name = 'ad_search';
    protected $_reload_on_wakeup   = FALSE;

    public function labels(){
        return array(
            'id'            => 'Id',
            'query'          => 'Запрос',
            'category_id'   => 'Категория',
            'cnt'           => 'Запросов',
        );
    }

    public function filters(){
        return array(
            'query' => array(
                array('trim', array(':value')),
                array('Text::trimall', array(':value')),
                array('Text::stripNL', array(':value')),
                array('strip_tags', array(':value')),
            ),
        );
    }

    public function getUri(){
        return Route::get('board_tag')->uri(array(
            'tagid' => $this->id,
            'cat_alias' => $this->category_id ? Model_BoardCategory::getField('alias', $this->category_id) : 'all',
        ));
    }

    /**
     * Find tag by query
     * @param $query
     * @param $category_id
     * @return ORM
     * @throws Kohana_Exception
     */
    public static function findTag($query, $category_id){
        $query = strip_tags($query);
        $query = trim($query);
        $query = Text::trimall($query);
        $query = mb_strtolower($query);

        return ORM::factory('BoardSearch')->where('query', '=', $query)->where('category_id', '=', $category_id)->find();
    }

    /**
     * Create new tag
     * @param $query
     * @param $category_id
     * @return bool
     */
    public static function createTag($query, $category_id){
        $query = strip_tags($query);
        $query = trim($query);
        $query = Text::trimall($query);
        $query = mb_strtolower($query);
        $tag = ORM::factory('BoardSearch')->values(array(
            'query' => $query,
            'category_id' => $category_id,
            'cnt' => 1,
        ))->save();
        return $tag->saved();
    }
}