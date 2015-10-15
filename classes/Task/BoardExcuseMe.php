<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board old ads cleaning
 */
class Task_BoardExcuseMe extends Minion_Task
{

    protected function _execute(Array $params){

        /********* PROTECTION ***********/
        die();
        /********* PROTECTION ***********/

        $start = time();
        Kohana::$environment = !isset($_SERVER['windir']) && !isset($_SERVER['GNOME_DESKTOP_SESSION_ID']) ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;

        $cfg = Kohana::$config->load('global');

        /*
         * update ads set addtime=(unix_timestamp() - 30.5 * 86400) where id=1952657
         */
        $ads = ORM::factory('BoardAd')
            ->where('publish','=', 1)
//            ->and_where('user_id','=', 1)
            ->and_where('addtime','>',DB::expr('UNIX_TIMESTAMP() - '. Date::DAY*31.5))
            ->and_where('addtime','<',DB::expr('UNIX_TIMESTAMP() - '. Date::DAY*30.5))
            ->find_all()
            ->as_array('id');
        foreach($ads as $ad){
            $user = $ad->user;
            if($user->email_verified){
                Email::instance()
                    ->reset()
                    ->to($ad->email)
                    ->from($cfg->robot_email)
                    ->subject($cfg['project']['name'] .': Технический сбой')
                    ->message(View::factory('board/mail/excuseme', array(
                        'site_name'=> $cfg['project']['name'],
                        'server_name'=> $cfg['project']['host'],
                        //                    'activation_link'=> Route::get('board_ad_confirm')->uri(array('id'=>$ad->id)),
                    ))->render()
                        , true)
                    ->send();
            }
        }

        print 'Operation taken '. (time() - $start) .' seconds'.PHP_EOL;
        print 'Total '. count($ads) .' notification sended '.PHP_EOL;
    }
}