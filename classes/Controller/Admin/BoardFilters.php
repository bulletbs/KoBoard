<?php

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
    );

    public $form_fields_save_extra = array(
        'parent_id'
    );

    public $_sort_fields = array(
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

    protected $_ordeby_field = 'ordr';
    protected $_ordeby_direction = 'ASC';

    public function action_index(){
        /* TYPE_ID filter field init */
        $this->_sort_fields['category_id']['data']['options'] = ORM::factory('BoardCategory')->getFullDepthArray();
        if(!$this->_sort_values['category_id'])
            $this->_sort_values['category_id'] = key($this->_sort_fields['category_id']['data']['options']);
        $this->_sort_fields['category_id']['data']['selected'] = $this->_sort_values['category_id'];

        /* Set category filter value (IN ARRAY)*/
        $parents = ORM::factory('BoardCategory')->getParentsId($this->_sort_values['category_id']);
        $parents[] = $this->_sort_values['category_id'];
        $this->_sort_values['category_id'] = $parents;

        parent::action_index();
    }

    /**
     * While form render - load form JS file
     * @param $model
     * @param array $data
     * @return array|bool|void
     */
    protected function _processForm($model, $data = array()){
        $this->scripts[] = 'media/js/admin/board/filter_form.js';

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
        }
        elseif($model->isOptional()){
            /* Save New Options */
            $newOptions = Arr::get($_POST,'newOptions', array());
            /* Child options saving with parent_id attribute */
            if($model->type == 5){
                foreach($newOptions as $parent_id=>$_options)
                    foreach($_options as $option)
                        ORM::factory('BoardOption')->values(array('value'=>$option, 'filter_id'=>$model->id, 'parent_id'=>$parent_id))->save();
            }
            else{
                foreach($newOptions as $option)
                    ORM::factory('BoardOption')->values(array('value'=>$option, 'filter_id'=>$model->id))->save();
            }

            /* Save New Options */
            $options = Arr::get($_POST,'options', array());
            foreach($options as $key=>$option)
                ORM::factory('BoardOption', $key)->values(array('value'=>$option))->save();

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
        if($type == 0){
            $view = 'admin/filters/boardListOptions';
            $data = array(
                'model' => ORM::factory('BoardFilter', $model_id),
            );
        }
        /* OPTIONLIST filter options*/
        elseif($type == 4){
            $view = 'admin/filters/boardListOptions';
            $data = array(
                'model' => ORM::factory('BoardFilter', $model_id),
            );
        }
        /* CHILDLIST filter options*/
        elseif($type == 5){
            $view = 'admin/filters/boardChildlistOptions';
            $model = ORM::factory('BoardFilter', $model_id);
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

        if(isset($view))
            return View::factory($view)->set($data)->render();
        return NULL;
    }
}