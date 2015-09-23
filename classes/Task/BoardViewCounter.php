<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board ads views count and update
 */
class Task_BoardViewCounter extends Minion_Task
{

    /**
     * Caching
     */
    protected function _execute(Array $params){
        $start = time();
        Kohana::$environment = !isset($_SERVER['windir']) && !isset($_SERVER['GNOME_DESKTOP_SESSION_ID']) ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;

        /**
         * Load views and clean cache
         */
        $str = '';
        $counter_file = Kohana::$cache_dir . DIRECTORY_SEPARATOR . 'BoardViewsPreCount.log';

        $fl = fopen($counter_file, 'c+');
        flock($fl, LOCK_EX);
        while(!feof($fl))
            $str .= fread($fl, 65535);
        ftruncate($fl, 0);
        flock($fl, LOCK_UN);
        fclose($fl);

        /**
         * Count and update views
         */
        foreach(explode(PHP_EOL, trim($str)) as $_id)
            $ids[$_id] = isset($ids[$_id]) ? $ids[$_id]+1 : 1;

        if(count($ids))
            foreach($ids as $_id=>$_views)
            DB::update()
                ->table('ads')
                ->set(array('views'=>DB::expr("views+".$_views)))
                ->where('id','=', $_id)
                ->execute()
            ;

        print 'Operation to update '. count($ids) .' ids taken '. (time() - $start) .' seconds'.PHP_EOL;
    }
}