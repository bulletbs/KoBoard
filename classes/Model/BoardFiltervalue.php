<?php

/**
 * Class Model_BoardFiltervalue
 * Model handling data from AD filter values
 */
class Model_BoardFiltervalue extends ORM{

    CONST OPTIONLIST_BYTES_LENGTH = 2;

    protected $_table_name = 'ad_filter_values';

	protected $_belongs_to = array(
        'ad' => array(
            'model' => 'BoardAd',
            'foreign_key' => 'ad_id',
        ),
        'filter' => array(
            'model' => 'BoardFilter',
            'foreign_key' => 'filter_id',
        ),
    );

    public function labels(){
        return array(
            'id'        => 'Id',
            'ad_id'     => 'Товар',
            'filter_id' => 'Фильтр',
            'value'     => 'Значение',
        );
    }


    /**
     * Parsing bit mask to array
     * @param $value
     * @return array
     */
    public static function bin2optlist($value){
        $optlist = array();
        for($i=0; $i < self::OPTIONLIST_BYTES_LENGTH * 8; $i++)
            $optlist[$i] = $value & 1<<$i;
        return $optlist;
    }


    /**
     * Parsing array to bit mask
     * @param $optlist
     * @return int
     */
    public static function optlist2bin($optlist){
        $mask = 0;
        foreach($optlist as $id=>$val)
            $mask |= $val<<$id;
        return $mask;
    }
}