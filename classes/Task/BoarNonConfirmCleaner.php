<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board old ads cleaning
 */
class Task_BoardNonConfirmCleaner extends Minion_Task
{

    protected function _execute(Array $params){
        $start = time();
        Kohana::$environment = !isset($_SERVER['windir']) && !isset($_SERVER['GNOME_DESKTOP_SESSION_ID']) ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;

        $ads = ORM::factory('BoardAd')
            ->where(DB::expr('addtime' + Date::WEEK),'<',time())
            ->where('publish','=', 0)
            ->where('key','<>', '')
            ->find_all()->as_array('id');
        foreach($ads as $ad){
            $ad->delete();
        }

        print 'Operation taken '. (time() - $start) .' seconds'.PHP_EOL;
        print 'Total '. count($ads) .' ads deleted'.PHP_EOL;
    }
}