<?php defined('SYSPATH') OR die('No direct script access.');

class Model_BoardFilter extends ORM{

    CONST SELECT_TYPE = 0;
    CONST NUMERIC_TYPE = 2;
    CONST OPTLIST_TYPE = 4;
    CONST CHILDLIST_TYPE = 5;
    CONST CHILDNUM_TYPE = 6;

    CONST CATEGORY_FILTERS_CACHE = 'BoardCategoryFiltersCache_';
    CONST MAIN_FILTERS_CACHE = 'BoardMainFiltersCache_';

    protected $_table_name = 'ad_category_filters';

	protected $_belongs_to = array(
        'category' => array(
            'model' => 'BoardCategory',
            'foreign_key' => 'category_id',
        ),
    );

    protected $_has_many = array(
        'options' => array(
            'model' => 'BoardOption',
            'foreign_key' => 'filter_id',
        ),
    );

    /**
     * List of filter type names
     * @var array
     */
    public $type_labels = array(
        0 => 'Список',
//        1 => 'Текстовый',
        2 => 'Числовой',
        3 => 'Да/Нет (checkbox)',
        4 => 'Список опций',
        5 => 'Список дочерний',
        6 => 'Числовой дочерний',
    );

    /**
     * List of filter types
     * @var array
     */
    public $type_list = array(
        0 => 'select',
//        1 => 'text',
        2 => 'digit',
        3 => 'checkbox',
        4 => 'optlist',
        5 => 'childlist',
        6 => 'childnum',
    );

    public function labels(){
        return array(
            'id'        => __('Id'),
            'category_id'   => __('Ad Category'),
            'parent_id'   => __('Parent filter'),
            'name'      => __('Name'),
            'units'      => __('Filter units'),
            'main'      => __('Main filter'),
            'ordr'      => __('Sort order'),
            'type'      => __('Filter type'),
            'options'   => __('Filter options'),
            'hints'   => __('Filter hints'),
            'no_digits'   => __('No digits'),
        );
    }

    /**
     * Check if filter type have option relation
     * 0 - list
     * 4 - option list
     * 5 - child list
     * @return bool
     */
    public function isOptional(){
        if(in_array($this->type, array(0, 4, 5)))
            return true;
        return false;
    }

    /**
     * Loading formated fiters array
     * @param $category
     * @return array
     */
    public static function loadFiltersByCategory($category){
        if(NULL === ($filters = Cache::instance()->get(self::CATEGORY_FILTERS_CACHE . $category))){
            $categories = ORM::factory('BoardCategory')->getParentsId($category);
            $categories[] = $category;
            $filters = array();
            foreach(ORM::factory('BoardFilter')->where('category_id','IN',$categories)->order_by('ordr')->find_all() as $filter){
                $filters[$filter->id]['name'] = $filter->name;
                $filters[$filter->id]['type'] = $filter->type_list[$filter->type];
//                $filters[$filter->id]['units'] = html_entity_decode($filter->units, ENT_NOQUOTES, 'UTF-8');
                $filters[$filter->id]['units'] = $filter->units;
                if($filter->main > 0)
                    $filters[$filter->id]['main'] = $filter->main;
                if($filter->isOptional()){
                    $filters[$filter->id]['options'] = array();
                    /* Load options related to selected parent option */
                    if(!$filter->parent_id){
                        $filters[$filter->id]['options'] = ORM::factory('BoardOption')->where('filter_id','=',$filter->id)->find_all()->as_array('id', 'value');
                        if($filter->main)
                            $filters[ $filter->id ]['options'] = Arr::merge(array(null=>$filter->name), $filters[$filter->id]['options']);
                    }
                }
                $filters[$filter->id]['parent'] = $filter->parent_id;
                if(isset($filters[ $filter->parent_id ]))
                    $filters[ $filter->parent_id ]['is_parent'] = TRUE;
                if($filter->type == self::NUMERIC_TYPE || $filter->type == self::CHILDNUM_TYPE){
                    $filters[$filter->id]['hints'] = $filter->hints;
                    $filters[$filter->id]['no_digits'] = $filter->no_digits;
                }
            }
            Cache::instance()->set(self::CATEGORY_FILTERS_CACHE . $category, $filters, Date::MONTH);
        }
        return $filters;
    }

    /**
     * Loads category main filter with options an options aliases
     * @param $category_id
     * @return array|mixed|ORM
     * @throws Cache_Exception
     * @throws Kohana_Exception
     */
    public static function loadMainFilter($category_id){
        if(NULL === ($filter = Cache::instance()->get(self::MAIN_FILTERS_CACHE . $category_id))) {
            $filter = ORM::factory('BoardFilter')->where('category_id', '=', $category_id)->and_where('main', '=', 1)->find();
            if ($filter->loaded()) {
                $filter = $filter->as_array();
                $options = ORM::factory('BoardOption')->where('filter_id', '=', $filter['id'])->order_by('value')->find_all();
                foreach($options->as_array() as $_filter){
                    $filter['options'][] = array(
                        'id' => $_filter->id,
                        'value' => $_filter->value,
                        'alias' => $_filter->alias,
                    );
                }
                $filter['aliases'] = $options->as_array('alias', 'id');
            }
            else
                $filter = FALSE;
            Cache::instance()->set(self::MAIN_FILTERS_CACHE . $category_id, $filter, Date::MONTH);
        }
        return $filter;
    }

    /**
     * Loading filter values by Ad ID
     * Loading child filter options related to selected parent option
     * @param array $filters
     * @param array $post
     * @param int $model_id
     */
    public static function loadFilterValues(&$filters, $post = array(), $model_id = NULL ){
        /* If GET request and model ID - loading values from DB */
        if($model_id && !count($post))
            $values = ORM::factory('BoardFiltervalue')->where('ad_id','=',$model_id)->find_all()->as_array('filter_id', 'value');

        foreach($filters as $id=>$filter){
            /* Setting values */
            if(isset($values[$id]))
                $filters[$id]['value'] = $filter['type'] == 'optlist' ? Model_BoardFiltervalue::bin2optlist($values[$id]) : $values[$id];
            elseif(isset($post[$id]))
                $filters[$id]['value'] = $post[$id];

            /* Add SELECT VALUE option in the top of list */
            if($filter['type'] == 'select'){
                $filters[$id]['options'] = Arr::merge(
                    array(NULL => __('Select one')),
                    $filters[$id]['options']
                );
            }
            /* Setting options if child list filter */
            if(isset($filter['parent']) && $filter['type']=='childlist' && isset($filters[$filter['parent']]) ){
                if(!isset($filters[ $filter['parent'] ]['value']) && count($filters[ $filter['parent'] ]['options']))
                    $filters[ $filter['parent'] ]['value'] = key($filters[ $filter['parent'] ]['options']);
                if(isset($filters[ $filter['parent'] ]['value'])){
                    $suboptions = self::loadSubfilterOptions($id, $filters[ $filter['parent'] ]['value']);
                    if(count($suboptions))
                        $filters[$id]['options'] = $suboptions;
                }
            }
        }
        return;
    }


    public static function loadSearchFilterValues(&$filters, $post){
        foreach($filters as $id=>$filter){
            /* Setting values */
            if(isset($post[$id]))
                $filters[$id]['value'] = $post[$id];

            /* Setting child options, adding ANY position in parent filter, parent default value is 0 (ANY) */
            if(isset($filter['parent']) && isset($filters[$filter['parent']])){
                /* Hide filter while main filter not selected */
                if(!isset($post[$filter['parent']]) || !$post[$filter['parent']] > 0){
                    unset($filters[$id]);
                    continue;
                }
                $filters[ $filter['parent'] ]['is_parent'] = TRUE;

                /* Setting child digit filter hints */
                if(isset($filter['parent']) && $filter['type']=='childnum' && isset($filters[$filter['parent']]['value']) ){
                    $filters[$id]['hints'] = self::loadSubFilterHints($id, $filters[ $filter['parent'] ]['value']);
                }
                /* Setting options if child num filter */
                if(isset($filter['parent']) && $filter['type']=='childlist' && isset($filters[$filter['parent']]) ){
                    $suboptions = self::loadSubfilterOptions($id, $filters[ $filter['parent'] ]['value'], TRUE);
                    if(count($suboptions))
                        $filters[$id]['options'] = Arr::merge(array(null=>$filter['name']), $suboptions);
                    else
                        unset($filters[$id]);
                }
            }
        }
        return;
    }

    /**
     * Load subfilter options
     * @param $id       - filter ID
     * @param $parent   - value of parent filter
     * @param $search   - TRUE if loading for search form (adding ANY option)
     * @return array
     */
    public static function loadSubFilterOptions($id, $parent, $search = false){
        $options = ORM::factory('BoardOption')->where('filter_id','=',$id)->and_where('parent_id', '=', $parent)->order_by('value', 'ASC')->find_all()->as_array('id', 'value');
//        if($search)
//            $options = Arr::merge(array(null=>__('Any')), $options);
        return $options;
    }

    /**
     * Load subfilter hints
     * @param $id       - filter ID
     * @param $parent   - value of parent filter
     * @param $search   - TRUE if loading for search form (adding ANY option)
     * @return array
     */
    public static function loadSubFilterHints($id, $parent){
        $hints = ORM::factory('BoardHint')->where('filter_id','=',$id)->and_where('parent_id', '=', $parent)->find()->as_array();
        return $hints['hints'];
    }

    /**
     * Generate URI to main filter
     * @param $alias
     * @return string
     * @throws Kohana_Exception
     */
    public static function generateUri($alias){
        $uri = Route::get('board_subcat')->uri(array(
            'cat_alias' => Request::$current->param('cat_alias'),
            'city_alias' => Request::$current->param('city_alias'),
            'filter_alias' => $alias,
        ));
        return $uri;
    }
}