<?php

/**
 * Class Model_BoardFiltervalue
 * Model handling data from AD filter values
 */
class Model_BoardFiltervalue extends ORM{

    CONST OPTIONLIST_BYTES_LENGTH = 2; // 16 options max

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
            $optlist[$i] = ($value & 1<<$i) ? 1 : 0;
        return $optlist;
    }


    /**
     * Parsing array to bit mask
     * @param $optlist
     * @return int
     */
    public static function optlist2bin($optlist){
        $mask = 0;
//        foreach($optlist as $id=>$val)
//            $mask |= $val<<$id;
        for($i=0; $i < self::OPTIONLIST_BYTES_LENGTH * 8; $i++)
            $mask |= isset($optlist[$i])<<$i;
        return $mask;
    }

    /**
     * Convert options array to binary mysql argument
     * @param $optlist
     * @return string
     */
    public static function optlist2mysqlBin($optlist){
        $bin = decbin(self::optlist2bin($optlist));
        $bin = "b'".$bin."'";
        return $bin;
    }

    /**
     * Check if filter value exists
     * @param $value
     * @return bool
     */
    public static function haveValue($value){
        if(is_array($value) && (isset($value['from']) || isset($value['to'])) )
            return (int) Arr::get($value, 'from') || (int) Arr::get($value, 'to');
        if(is_array($value) && count($value))
            return true;
        if(is_string($value) && !empty($value))
            return true;
        return false;

    }

    /**
     * Check filters array values exists
     * @param $values
     * @return bool
     */
    public static function haveValues($values){
        foreach($values as $value)
            if(self::haveValue($value))
                return true;
        return false;
    }

    /**
     * Echoes filter value from filter item array
     * received by Model_BoardFilter::loadFiltersByCategory
     * and Model_BoardFilter::loadFilterValues
     * @param $filter - $filters item array
     * @return null|string
     */
    public static function echoFiltersValues($filter){
        /* checkboxlist value */
        if($filter['type'] == 'optlist'){
            $values = array();
            foreach(array_values($filter['options']) as $opt_id=>$opt)
                if($filter['value'][$opt_id])
                    $values[] = $opt;
            return implode(', ', $values);
        }
        /* select options value */
        elseif($filter['type'] == 'select' || $filter['type'] == 'childlist'){
            if(isset($filter['options'][ $filter['value'] ]))
                return $filter['options'][ $filter['value'] ];
            return NULL;
        }
        /* simple value */
        return $filter['value'];
    }
}