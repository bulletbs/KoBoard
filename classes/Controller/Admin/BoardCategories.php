<?php

class Controller_Admin_BoardCategories extends Controller_Admin_Crud{

    public $submenu = 'adminBoardMenu';

    protected $_item_name = 'category';
    protected $_crud_name = 'Board categories';

    protected $_model_name = 'BoardCategory';

    /**
     * Actions with manual rendering
     * @var array
     */
    public $skip_auto_content_apply = array(
        'add',
        'edit',
        'import',
    );

    public $list_fields = array(
        'id',
        'name',
        'alias',
    );
    public $_form_fields = array(
        'name' => array('type'=>'text'),
        'alias' => array('type'=>'text'),
        'parent_id' => array('type'=>'select', 'data'=>array(
            'list'=>array(0=>'Корневая категория'),
            'selected'=>0,
        )),
        'subcats' => array('type'=>'call_view', 'data'=>'admin/categories/boardCategoryOptions'),
    );

    public $_sort_fields = array(
        'parent_id'=>array(
            'label' => 'Категория',
            'type' => 'select',
        ),
    );

    public function action_index(){
        /* Filter Parent_id initialize  */
        $this->_sort_fields['parent_id']['data']['options'][0] = 'Основные категории';
//        $root = ORM::factory($this->_model_name)->where('lvl', '=', 0)->find();
//        foreach($root->descendants() as $item)
        foreach(ORM::factory($this->_model_name)->roots() as $item)
            $this->_sort_fields['parent_id']['data']['options'][$item->id] = $item->getLeveledName(0);

        if(!isset($this->_sort_values['parent_id']))
            $this->_sort_values['parent_id'] = 0;
        $this->_sort_fields['parent_id']['data']['selected'] = $this->_sort_values['parent_id'];

        parent::action_index();
    }

    /**
     * While form render - load form JS file
     * @param $model
     * @param array $data
     * @return array|bool|void
     */
    protected function _processForm($model, $data = array()){
        $this->template->scripts[] = 'assets/board/js/admin/filter_form.js';

        /* Parent_id field intialize */
        $this->_form_fields['parent_id']['data']['options'][0] = 'Основные категории';
        $this->_form_fields['parent_id']['data']['options'] += ORM::factory('BoardCategory')->getFullDepthArray();
        if(!$model->loaded() && $parent_id = Arr::get($this->request->query(),'parent_id'))
            $this->_form_fields['parent_id']['data']['selected'] = $parent_id;
        else
            $this->_form_fields['parent_id']['data']['selected'] = $model->parent_id;

        parent::_processForm($model);
    }

    protected function _loadModel($id = NULL){
        $model = ORM::factory($this->_model_name, $id);

//        echo Debug::vars($model);
        return $model;
    }

    /**
     * @param $model ORM
     */
    protected function _saveModel($model){

        /* Changed parent: create new category or move present (by deleting and create new) */
        if(Arr::get($_POST, 'parent_id') && $model->parent_id != Arr::get($_POST, 'parent_id')){
            if($model->loaded())
                $model->delete();
            $parent = ORM::factory('BoardCategory', Arr::get($_POST, 'parent_id'));
            $model = ORM::factory('BoardCategory');
            $model->values(Arr::extract($_POST, array('name', 'alias')));
            if(empty($model->alias))
                $model->alias = Text::transliterate($model->name, true);
            $model->insert_as_last_child($parent);
        }
        else{
            $model->values(Arr::extract($_POST, array('name', 'alias')))->save();
        }

        /* Save New Options */
        foreach(Arr::get($_POST,'newOptions', array()) as $option){
            $newOption = ORM::factory('BoardCategory')->values(array('name'=>$option));
            $newOption->alias = Text::transliterate($newOption->name, true);
            $newOption->insert_as_last_child($model);
        }

        /* Save Present Options */
        echo Debug::vars($_POST);
        foreach(Arr::get($_POST,'options', array()) as $k=>$option){
            $option = ORM::factory('BoardCategory', $k)->values(array('name'=>$option));
            if(empty($option->alias))
                $option->alias = Text::transliterate($option->name, true);
            $option->update();
        }

        /* Delete Options */
        foreach(Arr::get($_POST,'deleted', array()) as $option)
            ORM::factory('BoardCategory', $option)->delete();

        Cache::instance()->delete('fullDepthCategories');
        Cache::instance()->delete('firstLevelCategories');
    }

    /**
     * Applying sort fields values
     * @param ORM $model
     */
    protected function _applyQueryFilters(ORM &$model){
        if(count($this->_sort_fields) && count($this->_sort_values))
            foreach($this->_sort_values as $k=>$v)
                if($v || $k=='parent_id')
                    $model->where(
                        $k ,
                        isset($this->_sort_fields[$k]['oper']) ? $this->_sort_fields[$k]['oper'] : '=',
                        isset($this->_sort_fields[$k]['oper']) && strtolower($this->_sort_fields[$k]['oper']) == 'like' ? "%{$v}%" : $v
                    );
    }

    /**
     *
     */
    public function action_import(){
        $categories = array();
        $result = DB::select()->from('jb_board_cat')->order_by('root_category', 'ASC')->order_by('id', 'ASC')->execute();
        foreach($result as $row){
            $categories[$row['root_category']][$row['id']] = $row;
        }
        foreach($categories[0] as $cat){
            Model_BoardCategory::import_category($categories, $cat);
        }
    }
}