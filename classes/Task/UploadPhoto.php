<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for upload import images
 */
class Task_UploadPhoto extends Minion_Task
{
    CONST LIMIT = 50;

    /**
     * Generate sitemaps
     */
    protected function _execute(Array $params){
//        set_time_limit(600);
	    Kohana::$environment = !isset($_SERVER['windir']) && !isset($_SERVER['GNOME_DESKTOP_SESSION_ID']) ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;
        $start = time();
        $imported = 0;

        /**
         * Importing
         */
        $photos = ORM::factory('BoardXmlPhoto')->order_by('id', 'ASC')->limit(self::LIMIT)->find_all();

	    $last_ad_id = 0;
	    $last_ad_key = 0;
        foreach ($photos as $key=>$photo) {
            $photo->uploadPhoto();
            if($last_ad_key > 0 && $last_ad_id != $photo->ad->id)
                $photos[$last_ad_key]->ad->setMainPhoto();
            $last_ad_key = $key;
            $last_ad_id = $photo->ad->id;
            $imported++;
        }
	    $photos[$last_ad_key]->ad->setMainPhoto();
        print 'Operation taken '. (time() - $start) .' seconds for '. $imported . ' records'.PHP_EOL;
    }
}