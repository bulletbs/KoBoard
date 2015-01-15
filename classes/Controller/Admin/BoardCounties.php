<?php

class Controller_Admin_BoardCounties extends Controller_Admin_Crud{

    public $submenu = 'adminBoardMenu';

    protected $_item_name = 'region';
    protected $_crud_name = 'Regions';

    protected $_model_name = 'BoardRegion';

    public $list_fields = array(
        'id',
        'name',
    );
    public $_form_fields = array(
        'name' => array('type'=>'text'),
        'alias' => array('type'=>'text'),
        'cities' => array('type'=>'call_view', 'data'=>'admin/counties/formOptions'),
    );

    /**
     * While form render - load form JS file
     * @param $model
     * @param array $data
     * @return bool|void
     */
    protected function _processForm($model, $data = array()){
        $this->template->scripts[] = 'assets/board/js/admin/region_form_options.js';

        parent::_processForm($model);
    }

    /**
     * @param $model ORM
     */
    protected function _saveModel($model){
        $model->values(Arr::extract($_POST, array_keys($this->_form_fields)));
        $model->save();

        /* Save New Options */
        $newOptions = Arr::get($_POST,'newOptions', array());
        foreach($newOptions as $option)
            ORM::factory('BoardCity')->values(array('name'=>$option, 'alias'=>NULL, 'region_id'=>$model->id))->save();

        /* Save New Options */
        $options = Arr::get($_POST,'options', array());
        foreach($options as $key=>$option)
            ORM::factory('BoardCity', $key)->values(array('name'=>$option, 'alias'=>NULL))->save();

        /* Delete Options */
        $deleteOptions = Arr::get($_POST,'deleted', array());
        foreach($deleteOptions as $option)
            ORM::factory('BoardCity', $option)->delete();
    }
}