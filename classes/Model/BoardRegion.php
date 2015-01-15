<?php

class Model_BoardRegion extends ORM{
    protected $_table_name = 'ad_regions';

    protected $_has_many = array(
        'cities' => array(
            'model'=>'BoardCity',
            'foreign_key'=>'region_id',
        ),
    );

    public function labels(){
        return array(
            'id' => __('Id'),
            'name' => __('Name'),
            'alias' => __('Alias'),
            'cities' => __('Cities'),
        );
    }

    public function filters(){
        return array(
            'alias' => array(
                array(array($this,'createAlias'))
            ),
        );
    }

    /**
     * Creates article alias and check if any dublicates exists
     * @param $alias
     * @return string
     */
    public function createAlias($alias){
        if(empty($alias))
            $alias = Text::transliterate($this->name, true);
        return $alias;
    }
}