<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board ads importation
 */
class Task_BoardImport extends Minion_Task
{
    CONST ALL_AMOUNT = 50000;
    CONST ONE_STEP_AMOUNT = 2000;
//    CONST ALL_AMOUNT = 50;
//    CONST ONE_STEP_AMOUNT = 10;

    /**
     * Generate sitemaps
     */
    protected function _execute(Array $params){
//        set_time_limit(600);
        Kohana::$environment = Kohana::PRODUCTION;
        $start = time();
        $amount = 0;
        $imported = 0;

        /**
         * Loading parents
         */
        $category_parents = array();
        $result = DB::select()->from('ad_categories')->order_by('id', 'ASC')->as_assoc()->execute();
        foreach($result as $res)
            $category_parents[$res['id']] = $res['parent_id'];
        $city_parents = array();
        $result = DB::select()->from('ad_cities')->order_by('id', 'ASC')->as_assoc()->execute();
        foreach($result as $res)
            $city_parents[$res['id']] = $res['parent_id'];

        /**
         * Importing
         */
        for($i=0; $i*self::ONE_STEP_AMOUNT < self::ALL_AMOUNT; $i++){
            $pos = DB::select(DB::expr('max(id) max'))->from('ads')->execute();

            $result = DB::select()
                ->from('jb_board')
                ->where('id','>', (int) $pos[0]['max'])
                ->order_by('id','ASC')
                ->limit(self::ONE_STEP_AMOUNT )
                ->as_assoc()->execute();
            $amount += count($result);
            print 'Taking '. ($i+1).' portion of '. self::ONE_STEP_AMOUNT  .' rows to import (ID from '. $pos[0]['max'] .')'.PHP_EOL;
            foreach($result as $row){
                try{
                    if(!isset( $city_parents[$row['city_id']] ))
                        continue;
                    $row['pcity_id'] = $city_parents[$row['city_id']];
                    if(!isset( $category_parents[$row['id_category']] ))
                        continue;
                    $row['pcategory_id'] = $category_parents[$row['id_category']];
                    Model_BoardAd::import_ad($row);
                    $imported++;
                }
                catch(ORM_Validation_Exception $e){
                    file_put_contents(DOCROOT . 'minion_error.log', $row['id'] .' - '. implode(', ', $e->errors('validation/error')).PHP_EOL, FILE_APPEND);
                }
            }
            unset($result);
        }

        print 'Operation taken '. (time() - $start) .' seconds for '. $imported . ' (of '. $amount .') records'.PHP_EOL;
    }
}