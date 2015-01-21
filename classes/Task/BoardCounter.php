<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board ads count caching
 */
class Task_BoardCounter extends Minion_Task
{

    /**
     * Caching
     */
    protected function _execute(Array $params){
        $start = time();
        Kohana::$environment = !isset($_SERVER['windir']) ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;

        $categories = ORM::factory('BoardCategory')->where('lvl','=','1')->find_all()->as_array('id','id');
        foreach($categories as $categoryid=>$category){
            $query = Model_BoardAd::boardOrmCounter()->where('pcategory_id','=',$category)->cached( Model_BoardAd::CACHE_TIME )->execute();
            print 'Category '. $categoryid .' have '.$query[0]['cnt'].' ads '.PHP_EOL;
            unset($query);
        }
        unset($categories);

        $cities = ORM::factory('BoardCity')->where('lvl','=','1')->find_all()->as_array('id','id');
        foreach($cities as $cityid=>$city){
            $query = Model_BoardAd::boardOrmCounter()->where('pcity_id','=',$city)->cached( Model_BoardAd::CACHE_TIME )->execute();
            print 'City '. $cityid .' have '.$query[0]['cnt'].' ads '.PHP_EOL;
            unset($query);
        }
        unset($cities);

        print 'Operation taken '. (time() - $start) .' seconds'.PHP_EOL;
    }
}