<?php

class Model_BoardHint extends ORM{

    protected $_table_name = 'ad_category_filter_hints';

    protected $_belongs_to = array(
        'filter' => array(
            'model' => 'BoardFilter',
            'foreign_key' => 'filter_id',
        ),
    );

    public function rules(){
        return array();
    }

    public function labels(){
        return array(
            'id'        => 'Id',
            'filter_id'   => 'Filter',
            'parent_id'   => 'Parent Option',
            'hints'      => 'Filter hints',
        );
    }
}