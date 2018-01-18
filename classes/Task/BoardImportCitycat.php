<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board city and category importation
 */
class Task_BoardImportCitycat extends Minion_Task
{
    /**
     * Importing categories and cities
     */
    protected function _execute(Array $params){
        $start = time();
        Kohana::$environment = Kohana::PRODUCTION;

        /**
         * Importing cities
         */
        $cities = array();
        $result = DB::select()->from('jb_city')->execute();
        foreach($result as $row){
            $cities[$row['parent']][$row['id']] = $row;
        }
        foreach($cities[0] as $cat){
            Model_BoardCity::import_city($cities, $cat);
        }

        /**
         * Importing categories
         */
        $categories = array();
        $result = DB::select()->from('jb_board_cat')->order_by('root_category', 'ASC')->order_by('id', 'ASC')->execute();
        foreach($result as $row){
            $categories[$row['root_category']][$row['id']] = $row;
        }
        foreach($categories[0] as $cat){
            Model_BoardCategory::import_category($categories, $cat);
        }

        print 'Operation taken '. (time() - $start) .' seconds'.PHP_EOL;
    }
}