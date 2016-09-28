<?php

class Controller_Admin_BoardTags extends Controller_Admin_Crud{

    public $submenu = 'adminBoardMenu';

    protected $_item_name = 'tag';
    protected $_crud_name = 'Board tags';

    protected $_model_name = 'BoardSearch';

    protected $_orderby_field = 'cnt';
    protected $_orderby_direction= 'DESC';


    public $list_fields = array(
        'id',
        'query',
        'cnt',
    );

    protected $_sortable_fields = array(
        'query'=>true,
        'cnt'=>true,
    );

    protected $_multi_operations = array(
        'delete_sel' => 'Delete selected',
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
     * Добавление множества тегов
     * @throws HTTP_Exception_404
     * @throws Kohana_Exception
     */
    public function action_add()
    {
        if(Request::current()->method() == Request::POST){
            /* Process POST */
            if(isset($_POST['cancel'])){
                $this->go($this->_crud_uri . URL::query());
            }
            $tags = explode(PHP_EOL, Arr::get($_POST, 'tags', array()));
            $cnt = Arr::get($_POST, 'cnt', 0);
            $category_id = Arr::get($_POST, 'category_id', 0);
            if(count($tags)){
                try{
                    foreach($tags as $_tag)
                        ORM::factory($this->_model_name)->values(array(
                            'query' => $_tag,
                            'cnt' => $cnt,
                            'category_id' => $category_id,
                        ))->save();
                    Flash::success('Теги ('.implode(',', $tags).') успешно добавлены');
                }
                catch(ORM_Validation_Exception $e){
                    Flash::error($e->getMessage());
                }
            }
            $this->redirect( $this->_crud_uri . URL::query(array('category_id'=>$category_id)));
        }
        $category_options[0] = 'Без категории';
        $category_options['Разделы'] = (array) ORM::factory('BoardCategory')->where('parent_id', '=', 0)->order_by('name', 'ASC')->find_all()->as_array('id', 'name');
        $category_options += Model_BoardCategory::getTwoLevelArray();
        $this->template->content = $this->getContentTemplate('admin/tags/add')->set(array(
            'category_options' => $category_options,
        ));
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

    protected function _multi_delete_sel($ids){
        $rows = DB::delete(ORM::factory($this->_model_name)->table_name())->where('id','IN',$ids)->execute();
        Flash::success('Теги успешно удалены ('.$rows.')');
    }
}