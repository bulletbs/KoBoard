<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board old ads cleaning
 */
class Task_BoardAdsCleaner extends Minion_Task
{

    protected function _execute(Array $params){
        $start = time();
        Kohana::$environment = !isset($_SERVER['windir']) && !isset($_SERVER['GNOME_DESKTOP_SESSION_ID']) ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;

        $cfg = Kohana::$config->load('global');

        $ads = ORM::factory('BoardAd')
            ->where(DB::expr('addtime' + Date::DAY*30),'>',time())
            ->where('publish','=', 1)
            ->and_where('user_id','=', 1)
            ->find_all()->as_array('id');
        foreach($ads as $ad){
            $ad->publish = 0;
            $ad->update();
            Email::instance()
                ->to($ad->email)
                ->from($cfg->robot_email)
                ->subject(html_entity_decode($cfg['project']['name']) .': '. __('Old classified out of date'))
                ->message(View::factory('board/mail/ad_outofdate', array(
                    'user'=>$ad->name,
                    'title'=>$ad->title,
                    'site_name'=> $cfg['project']['name'],
                    'server_name'=> $cfg['project']['host'],
//                    'activation_link'=> Route::get('board_ad_confirm')->uri(array('id'=>$ad->id)),
                ))->render()
                    , true)
                ->send();
        }

        print 'Operation taken '. (time() - $start) .' seconds'.PHP_EOL;
        print 'Total '. count($ads) .' notification sended '.PHP_EOL;
    }
}