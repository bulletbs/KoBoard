<?php

class Controller_Admin_BoardTags extends Controller_Admin_Crud{

    public $submenu = 'adminBoardMenu';

    protected $_item_name = 'tag';
    protected $_crud_name = 'Board tags';

    protected $_model_name = 'BoardSearch';

    public $list_fields = array(
        'id',
        'query',
        'cnt',
    );

    /**
     * Actions with manual rendering
     * @var array
     */
    public $skip_auto_content_apply = array(
        'add',
        'edit',
    );

    public $_form_fields = array(
        'query' => array('type'=>'text'),
        'category_id' => array('type'=>'select', 'data'=>array(
            'selected'=>0,
        )),
        'cnt' => array('type'=>'digit'),
    );

    public $_filter_fields = array(
        'query'=>array(
            'label' => 'По тегу',
            'type' => 'text',
        ),
        'category_id'=>array(
            'label' => 'Категория',
            'type' => 'select',
        ),
    );

    public function action_index(){
        /* Filter Parent_id initialize  */
        $this->_filter_fields['category_id']['data']['options'][0] = 'Все категории';
        $this->_filter_fields['category_id']['data']['options']['Разделы'] = (array) ORM::factory('BoardCategory')->where('parent_id', '=', 0)->order_by('name', 'ASC')->find_all()->as_array('id', 'name');
        $this->_filter_fields['category_id']['data']['options'] += Model_BoardCategory::getTwoLevelArray();
        if(!isset($this->_filter_values['category_id']))
            $this->_filter_values['category_id'] = 0;
        $this->_filter_fields['category_id']['data']['selected'] = $this->_filter_values['category_id'];

        parent::action_index();
    }

    /**
     * While form render - load form JS file
     * @param $model
     * @param array $data
     * @return array|bool|void
     */
    protected function _processForm($model, $data = array()){
        /* Parent_id field intialize */
        $this->_form_fields['category_id']['data']['options'] = ORM::factory('BoardCategory')->getFullDepthArray();
        if(!$model->loaded() && $category_id = Arr::get($this->request->query(),'category_id'))
            $this->_form_fields['category_id']['data']['selected'] = $category_id;
        else
            $this->_form_fields['category_id']['data']['selected'] = $model->category_id;

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
            $model->values(Arr::extract($_POST, array('query', 'category_id', 'cnt')))->save();
    }
}