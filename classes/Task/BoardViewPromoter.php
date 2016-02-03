<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board ads views promote
 */
class Task_BoardViewPromoter extends Minion_Task
{
    protected function _execute(Array $params){
        $start = time();
//        Kohana::$environment = !isset($_SERVER['windir']) && !isset($_SERVER['GNOME_DESKTOP_SESSION_ID']) ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;

        $table = ORM::factory('BoardAd')->table_name();
        $year_now = array(
            strtotime('1.01.' . date('Y')),
            strtotime('31.12.' . date('Y')),
        );
        $year_prev = array(
            strtotime('1.01.' . (date('Y')-1)),
            strtotime('31.12.' . (date('Y')-1)),
        );

        /**
         * Load views and clean cache
         */
        $ids = DB::select('id')->from($table)->where('addtime','BETWEEN', array($year_prev[0], $year_now[1]))->and_where('publish', '=', '1')->execute();
        foreach($ids as $_id){
            DB::update($table)->set(array('views'=>DB::expr('views+' . mt_rand(3, 15))))->where('id','=',$_id)->execute();
        }

        print 'Operation to update '. count($ids) .' ids taken '. (time() - $start) .' seconds'.PHP_EOL;
    }
}