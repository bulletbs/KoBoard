<?php

class Model_BoardAbuse extends ORM{

    public static $types = array(
        0 => 'Не соответствует морали',
        1 => 'Неверная рубрика',
        2 => 'Нарушение закона',
        3 => 'Спам',
        4 => 'Не актуально',
    );

    protected $_table_name = 'ad_abuses';

    protected $_belongs_to = array(
        'ad' => array(
            'model' => 'BoardAd',
            'foreign_key' => 'ad_id',
        ),
    );

    public function rules(){
        return array();
    }

    public function labels(){
        return array(
            'id'        => 'Id',
            'ad_id'   => 'Complainted ad',
            'type'      => 'Complaint type',
        );
    }
}