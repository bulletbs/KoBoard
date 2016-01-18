<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board ads importation
 */
class Task_BoardParentSetter extends Minion_Task
{
    CONST ALL_AMOUNT = 100000;
    CONST ONE_STEP_AMOUNT = 5000;

    /**
     * Generate sitemaps
     */
    protected function _execute(Array $params){
        set_time_limit(600);
        Kohana::$environment = Kohana::PRODUCTION;

        $start = time();
        $amount = 0;
        $deleted = 0;

        $categories = ORM::factory('BoardCategory')->find_all()->as_array('id');
        $cities = ORM::factory('BoardCity')->find_all()->as_array('id');
        $result = DB::select(
            'id',
            'city_id',
            'category_id'
        )->from('ads')->where('pcity_id','=','0')->limit(self::ALL_AMOUNT)->as_assoc()->execute();
        foreach($result as $row){
            if(isset($categories[$row['category_id']]) && isset($cities[$row['city_id']])){
                $values = array(
                    'pcategory_id' => $categories[$row['category_id']]->parent_id,
                    'pcity_id' => $cities[$row['city_id']]->parent_id,
                );
                DB::update('ads')->set($values)->where('id','=', $row['id'])->execute();
                $amount++;
            }
            else{
                DB::delete('ads')->where('id','=', $row['id'])->execute();
                $deleted++;
            }
        }

        print 'Operation taken '. (time() - $start) .' seconds for '.  $amount  . ' records ('.$deleted.' deleted)'.PHP_EOL;
    }
}