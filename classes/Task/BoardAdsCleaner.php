<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board old ads cleaning
 */
class Task_BoardAdsCleaner extends Minion_Task
{
    public $sended = 0;

    /* Список дней напоминания */
    public $remind_on_days = array(
        '30',
        '60',
        '90',
        '120',
    );

    protected function _execute(Array $params){
        $start = time();
        Kohana::$environment = !isset($_SERVER['windir']) && !isset($_SERVER['GNOME_DESKTOP_SESSION_ID']) ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;

        $cfg = Kohana::$config->load('global');

        /*
         * update ads set addtime=(unix_timestamp() - 30.5 * 86400) where id=1952657
         */

        $user_ads = array();
        foreach($this->remind_on_days as $days) {
            $ads = ORM::factory('BoardAd')
                ->where('publish', '=', 1)
//                ->and_where('user_id', '=', 1)
                ->and_where('addtime', '>', DB::expr('UNIX_TIMESTAMP() - ' . Date::DAY * ($days + 1)))
                ->and_where('addtime', '<', DB::expr('UNIX_TIMESTAMP() - ' . Date::DAY * $days))
                ->find_all()
                ->as_array('id');

            /* Grouping ADs */
            foreach ($ads as $ad)
                $user_ads[$ad->user_id][$days][$ad->id] = $ad;
        }

        /* Sending reminds */
        foreach($user_ads as $user_id=>$ads){
//            $ad->publish = 0;
//            $ad->update();
            $user = ORM::factory('User', $user_id);
            if($user->email_verified){
                Email::instance()
                    ->reset()
                    ->to($ad->email)
                    ->from($cfg->robot_email)
                    ->subject($cfg['project']['name'] .': '. __('Old classifieds out of date'))
                    ->message(View::factory('board/mail/ad_refresh_reminder', array(
                        'user_ads'=>$ads,
                        'site_name'=> $cfg['project']['name'],
                        'server_name'=> $cfg['project']['host'],
                        //                    'activation_link'=> Route::get('board_ad_confirm')->uri(array('id'=>$ad->id)),
                    ))->render()
                        , true)
                    ->send();
                $this->sended++;
            }
        }

        print 'Operation taken '. (time() - $start) .' seconds'.PHP_EOL;
        print 'Total '. $this->sended .' notification sended '.PHP_EOL;
    }
}