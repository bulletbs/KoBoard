<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Admin_BoardFilters extends Controller_Admin_Crud{

    public $submenu = 'adminBoardMenu';

    protected $_item_name = 'filter';
    protected $_crud_name = 'Category filters';

    protected $_model_name = 'BoardFilter';
    protected $_order_field = 'ordr';

    public $list_fields = array(
        'id',
        'name',
    );

    public $_form_fields = array(
        'name' => array('type'=>'text'),
        'units' => array('type'=>'text'),
        'main' => array('type'=>'checkbox'),
        'category_id' => array('type'=>'select', 'data'=>array(
            'list'=>array(),
            'selected'=>0,
        )),
        'type' => array('type'=>'select', 'data'=>array(
            'list'=>array(),
            'selected'=>0,
        )),
        'options' => array(
            'type'=>'call_view',
            'data'=>'admin/filters/boardFilter',
            'advanced_data'=>array(
                'preloaded'=>'',
            )
        ),
        'hints' => array('type'=>'text'),
        'no_digits' => array('type'=>'checkbox'),
    );

    public $form_fields_save_extra = array(
        'parent_id',
    );

    public $_filter_fields = array(
        'category_id' => array(
            'type'=>'select',
            'label'=>'Категория',
            'oper'=>' IN ',
        ),
//        'name' => array(
//            'type'=>'text',
//            'label'=>'Contains',
//            'oper'=>'like',
//        ),
    );

    protected $_setorder_field = 'ordr';

    protected $_orderby_field = 'ordr';
    protected $_orderby_direction = 'ASC';

    public function before(){
        $this->skip_auto_render[] = 'showGeo';
        $this->skip_auto_render[] = 'showCats';
        $this->skip_auto_render[] = 'showSubCats';
        $this->skip_auto_render[] = 'showFilters';
        $this->skip_auto_render[] = 'showParams';
        $this->skip_auto_render[] = 'filtersFromJSON';
        parent::before();
    }

    public function action_index(){
        /* TYPE_ID filter field init */
        $this->_filter_fields['category_id']['data']['options'] = ORM::factory('BoardCategory')->getFullDepthArray();
        if(!$this->_filter_values['category_id'])
            $this->_filter_values['category_id'] = key($this->_filter_fields['category_id']['data']['options']);
        $this->_filter_fields['category_id']['data']['selected'] = $this->_filter_values['category_id'];

        /* Set category filter value (IN ARRAY)*/
        $parents = ORM::factory('BoardCategory')->getParentsId($this->_filter_values['category_id']);
        $parents[] = $this->_filter_values['category_id'];
        $this->_filter_values['category_id'] = $parents;

        parent::action_index();
    }

    /**
     * While form render - load form JS file
     * @param $model
     * @param array $data
     * @return array|bool|void
     */
    protected function _processForm($model, $data = array()){
        $this->scripts[] = 'assets/board/js/admin/filter_form.js';

        /* Getting options for category list */
        $this->_form_fields['category_id']['data']['options'] = ORM::factory('BoardCategory')->getFullDepthArray();
        $this->_form_fields['category_id']['data']['selected'] = $model->loaded() ? $model->category->id : Arr::get($_GET,'category_id');

        /* Getting options for filter type list */
        $this->_form_fields['type']['data']['options'] = $model->type_labels;
        $this->_form_fields['type']['data']['selected'] = $model->loaded() ? $model->type : 0;

        /* hidding options list if not LIST type selected */
        if($model->loaded()){
            $this->_form_fields['options']['hidden'] = TRUE;
            $this->_form_fields['options']['advanced_data']['preloaded'] = $this->_renderFilterOptions($model->type, $model->id);
        }
        else
            $this->_form_fields['options']['advanced_data']['preloaded'] = $this->_renderFilterOptions(0, NULL);


        parent::_processForm($model);
    }

    /**
     * @param $model ORM
     */
    protected function _saveModel($model){
        parent::_saveModel($model);

        /* If filter type changes - clear filter options */
        /* Then save list options, if filter type is select */
        if($model->loaded() && $model->changed('type') && $model->type > 0){
            ORM::factory('BoardOption')->where('filter_id','=',$model->id)->delete();
            ORM::factory('BoardHint')->where('filter_id','=',$model->id)->delete();
        }
        /* Child hints */
        if($model->type == Model_BoardFilter::CHILDNUM_TYPE){
            $exists = ORM::factory('BoardHint')->where('filter_id','=',$model->id)->find_all()->as_array('parent_id');
            $hints = Arr::get($_POST,'childhints', array());
            foreach($hints as $parent_id=>$_hint){
                $hint_model = isset($exists[$parent_id]) ? $exists[$parent_id] : ORM::factory('BoardHint');
                $hint_model->values(array('hints'=>$_hint, 'filter_id'=>$model->id, 'parent_id'=>$parent_id))->save();
            }
        }
        elseif($model->isOptional()){
            /* Save New Options */
            $newOptions = Arr::get($_POST,'newOptions', array());
            /* Child options saving with parent_id attribute */
            if($model->type == Model_BoardFilter::CHILDLIST_TYPE){
                foreach($newOptions as $parent_id=>$_options)
                    foreach($_options as $option)
                        ORM::factory('BoardOption')->values(array('value'=>$option, 'filter_id'=>$model->id, 'parent_id'=>$parent_id))->save();
            }
            else{
                foreach($newOptions as $option)
                    ORM::factory('BoardOption')->values(array('value'=>$option, 'filter_id'=>$model->id, 'alias'=> $model->main ? Text::transliterate($option, true) : NULL ))->save();
            }

            /* Save New Options */
            $options = Arr::get($_POST,'options', array());
            foreach($options as $key=>$option)
                ORM::factory('BoardOption', $key)->values(array('value'=>$option, 'alias'=> $model->main ? Text::transliterate($option, true) : NULL ))->save();

            /* Delete Options */
            $deleteOptions = Arr::get($_POST,'deleted', array());
            foreach($deleteOptions as $option)
                ORM::factory('BoardOption', $option)->delete();

        }
    }

    /**
     * AJAX: Loading options section for selected filter type
     */
    public function action_getoptions(){
        if(!$this->request->is_ajax())
            $this->go();

        $this->json['content'] = '';
        $type_id = $this->request->post('type_id');
        $model_id = $this->request->post('model_id');

        /* LIST filter options*/
        $this->json['status'] = TRUE;
        if($type_id !== NULL){
            $this->json['content'] = $this->_renderFilterOptions($type_id, $model_id);
        }

    }

    /**
     * AJAX: Loading parent options list
     */
    public function action_parentoptions(){
        if(!$this->request->is_ajax())
            $this->go();
        $this->json['content'] = '';
        $model_id = $this->request->post('model_id');
        $parent_id = $this->request->post('parent_id');
        $this->json['status'] = TRUE;
        if($parent_id)
            $this->json['content'] = $this->_renderFilterOptions(5, $model_id, $parent_id);
    }

    /**
     * Render filter options views
     * @param $type - Filter type index
     * @param $model_id - Filter ID
     * @param $parent_id - used in type 5 only
     * @return string
     */
    protected function _renderFilterOptions($type, $model_id, $parent_id = NULL){
        if($type == Model_BoardFilter::SELECT_TYPE){
            $view = 'admin/filters/boardListOptions';
            $data = array(
                'model' => ORM::factory('BoardFilter', $model_id),
            );
        }
        /* OPTIONLIST filter options*/
        elseif($type == Model_BoardFilter::OPTLIST_TYPE){
            $view = 'admin/filters/boardListOptions';
            $data = array(
                'model' => ORM::factory('BoardFilter', $model_id),
            );
        }
        /* CHILDLIST filter options*/
        elseif($type == Model_BoardFilter::CHILDLIST_TYPE){
            $view = 'admin/filters/boardChildlistOptions';
            $model = ORM::factory('BoardFilter', $model_id);
            if(!$model->category_id && Arr::get('category_id', $_POST))
                $model->category_id = Arr::get('category_id', $_POST);
            if($this->request->post('category_id'))
                $model->category_id = $this->request->post('category_id');
            $parent_filters = ORM::factory('BoardFilter')->where('type', '=', 0)->and_where('parent_id', '=', 0)->and_where('category_id', '=', $model->category_id)->order_by('ordr')->find_all()->as_array('id', 'name');
            if(!count($parent_filters)){
                return "Нет родительских фильтров в выбраной категории";
            }

            if($parent_id)
                $model->parent_id = $parent_id;
            if(!$model->parent_id)
                $model->parent_id = key($parent_filters);

            $parent_options = ORM::factory('BoardOption')->where('filter_id','=',$model->parent_id)->order_by('value')->find_all()->as_array('id', 'value');

            $options = array();
            foreach(ORM::factory('BoardOption')->where('filter_id','=',$model->id)->order_by('value')->find_all() as $option)
                $options[$option->parent_id][$option->id] = $option;
            
            $data = array(
                'model' => $model,
                'parent_filters' => $parent_filters,
                'parent_options' => $parent_options,
                'options' => $options,
            );
        }

        /* DIGIT CHILD FILTER HINTS  */
        elseif($type == Model_BoardFilter::CHILDNUM_TYPE){
            $view = 'admin/filters/boardChildnumHints';
            $model = ORM::factory('BoardFilter', $model_id);

            $parent_filters = ORM::factory('BoardFilter')->where('type', '=', 0)->and_where('parent_id', '=', 0)->and_where('category_id', '=', $model->category_id)->order_by('ordr')->find_all()->as_array('id', 'name');
            if(!count($parent_filters)){
                return "Нет родительских фильтров в выбраной категории";
            }
            if($parent_id)
                $model->parent_id = $parent_id;
            if(!$model->parent_id)
                $model->parent_id = key($parent_filters);
            $parent_options = ORM::factory('BoardOption')->where('filter_id','=',$model->parent_id)->order_by('value')->find_all()->as_array('id', 'value');
            $hints = array();
            $values = ORM::factory('BoardHint')->where('filter_id','=',$model->id)->find_all()->as_array('parent_id', 'hints');
            foreach($parent_options as $opt_id => $opt)
                $hints[$opt_id] = Arr::get($values, $opt_id);
            $data = array(
                'model' => $model,
                'parent_filters' => $parent_filters,
                'parent_options' => $parent_options,
                'hints' => $hints,
            );
        }

        if(isset($view))
            return View::factory($view)->set($data)->render();
        return NULL;
    }

    /* Show OLX GEO data */
    public function action_showGeo(){
        $str = file_get_contents(MODPATH.'board/data/geotop.json');
        $var = json_decode($str);
        echo Debug::vars( $var );
    }

    /* Show OLX CATEGORIES data */
    public function action_showCats(){
        $str = file_get_contents(MODPATH.'board/data/categories.json');
        $var = json_decode($str);
        echo Debug::vars( $var );
    }

    /* Show OLX SUBCATEGORIES data */
    public function action_showSubCats(){
        $str = file_get_contents(MODPATH.'board/data/subcategories.json');
        $var = json_decode($str);
        echo Debug::vars( $var );
    }

    /* Show OLX FILERS data */
    public function action_showFilters(){
        $str = file_get_contents(MODPATH.'board/data/filters.json');
        $var = json_decode($str);
        echo Debug::vars( $var );
    }

    /* Show OLX FILERS data */
    public function action_showParams(){
        $str = file_get_contents(MODPATH.'board/data/params.json');
        $var = json_decode($str);
        echo Debug::vars( $var );
    }

    public function action_filtersFromJSON(){
        DB::delete('ad_category_filters')->execute();
        DB::delete('ad_category_filter_options')->execute();
        DB::query(NULL, 'ALTER TABLE ad_category_filters AUTO_INCREMENT=1')->execute();
        DB::query(NULL, 'ALTER TABLE ad_category_filter_options AUTO_INCREMENT=1')->execute();

        $olx_to_our = array();
        $olx_to_filter = array();
        $olx_to_option = array();
        $subfilter_to_our = array();
        $filter_params = array();
        $filter_cats = array();

        $our_cats = ORM::factory('BoardCategory')->find_all()->as_array('name', 'id');

        $cats = json_decode(file_get_contents(MODPATH.'board/data/categories.json'));
        $subcats = json_decode(file_get_contents(MODPATH.'board/data/subcategories.json'));
        $filters = json_decode(file_get_contents(MODPATH.'board/data/filters.json'));

        $params = json_decode(file_get_contents(MODPATH.'board/data/params.json'));
        foreach($params as $param)
            $filter_params[$param->parameter->key] = $param;

        /* setting our cat ids */
        foreach($cats as $catid=>$cat){
            if(isset($our_cats[$cat->name]))
                $olx_to_our[$cat->id] = $our_cats[$cat->name];
            foreach($cat->children as $subcatid=>$subcat){
                if(isset($our_cats[$subcat->name]))
                    $olx_to_our[$subcat->id] = $our_cats[$subcat->name];
            }
        }

        /* create first level filters */
        foreach($subcats as $filters_id => $_filters){
            if(isset($olx_to_our[$filters_id])){
                $label = ORM::factory('BoardFilter')->values(array(
                    'category_id' => $olx_to_our[$filters_id],
                    'name' => $_filters->search_label,
                    'main' => 1,
                ))->save();

                foreach($_filters->children as $option_id => $option){
                    $value = ORM::factory('BoardOption')->values(array(
                        'filter_id' => $label->pk(),
                        'value' => $option->label,
                        'alias' => $option->code,
                    ))->save();
                        $olx_to_option[$option_id] = $value->pk();
                    $filter_cats[$option_id] = $olx_to_our[$filters_id];
                    $olx_to_filter[$option_id] = $label->pk();
                }
            }
        }

//        echo Debug::vars($olx_to_option);
        /* create second level filters and parameter-filters */
        foreach($filters as $filters_id => $_filters){
            $param_id = each($_filters->k);
            $param_id = $param_id['key'];
            $param = $filter_params[ $param_id ];
            $category = each($param->categories);
            $category = $category['key'];

            /* Getting our category */
            $our_category = 0;
            if(isset($olx_to_filter[$category])){
                $our_category = $filter_cats[$category];
                $parent_filter_id = $olx_to_filter[$category];
//                if($category == 264){
//                    echo Debug::vars($our_category);
//                }
            }
            elseif(isset($olx_to_our[$category])){
                $our_category = $olx_to_our[$category];
                $parent_filter_id = $filters_id * -1;
            }

            /* Create filters */
            if($our_category){
                /* Create filter label */
                if(!isset($subfilter_to_our[$parent_filter_id])){
                    $label = ORM::factory('BoardFilter')->values(array(
                        'category_id' => $our_category,
                        'name' => $param->parameter->label,
                        'type' => isset($olx_to_option[$category]) ? Model_BoardFilter::CHILDLIST_TYPE : Model_BoardFilter::SELECT_TYPE ,
                    ));
                    if(isset($olx_to_filter[$category]))
                        $label->set('parent_id', $olx_to_filter[$category]);
                    $label->save();
                    $subfilter_to_our[$parent_filter_id] = $label->pk();
                }

                /* Create filter options */
                foreach($_filters->v as $option_id => $option){
                    $value = ORM::factory('BoardOption')->values(array(
                        'filter_id' => $subfilter_to_our[$parent_filter_id],
                        'value' => $option,
                    ));
                    if(isset($olx_to_option[$category])){
                        $value->set('parent_id', $olx_to_option[$category]);
                    }
                    $value->save();
                }
            }
        }

        /* Create numerical filters */
        $ignore_params = array(
            'filter_both_price' => 1,
        );
        foreach($params as $param_id=>$_param){
            if($_param->parameter->isNumeric){
                foreach($_param->categories as $category=>$tmp){
                    if(isset($olx_to_our[$category]) && !isset($ignore_params[$_param->parameter->solr_column_name])){
                        $label = ORM::factory('BoardFilter')->values(array(
                            'category_id' => $olx_to_our[$category],
                            'name' => $_param->parameter->label,
                            'units' => $_param->parameter->suffix,
                            'type' => Model_BoardFilter::NUMERIC_TYPE,
                        ));
                        $label->save();
                    }
                }
            }
        }
    }
}