<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board old ads cleaning
 */
class Task_BoardTagsCleaner extends Minion_Task
{

    protected function _execute(Array $params){
        $start = time();
        Kohana::$environment = !isset($_SERVER['windir']) && !isset($_SERVER['GNOME_DESKTOP_SESSION_ID']) ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;

        $count = DB::delete(ORM::factory('BoardSearch')->table_name())->where('cnt','<', 3)->or_where(DB::expr('LENGTH(query)'),'>',30)->execute();

        print 'Done. '. $count .' tags deleted '.PHP_EOL;
        print 'Operation taken '. (time() - $start) .' seconds'.PHP_EOL;
    }
}