<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Created by JetBrains PhpStorm.
 * User: butch
 * Date: 23.05.12
 * Time: 18:35
 * To change this template use File | Settings | File Templates.
 */
class Controller_Admin_BoardSender extends Controller_Admin_UserSender
{
    CONST MAILER_MODEL = 'BoardMailer';
    CONST MAILER_LETTER_TEMPLATE = 'board/mail/ad_refresh_reminder';
    CONST MAILER_LIMIT = 10000;

    CONST MAILER_DAYS_AGO = 30;

    public $skip_auto_content_apply = array(
        'create',
        'send',
        'sendtest',
    );

    public function action_index(){
        $this->scripts[] = "media/libs/bootstrap/js/bootbox.min.js";
        $this->scripts[] = "media/libs/bootstrap/js/bbox_".I18n::$lang.".js";

        $letter = View::factory(self::MAILER_LETTER_TEMPLATE)->set(array(
            'user_ads' => array(self::MAILER_DAYS_AGO=>ORM::factory('BoardAd')->limit(1)->find_all()),
            'site_name'=> KoMS::config()->project['name'],
            'server_name'=> $_SERVER['HTTP_HOST'],
            'unsubscribe_link' => Model_User::generateCryptoLink('unsubscribe', $this->current_user->id),
        ));
        $total = ORM::factory(self::MAILER_MODEL)->count_all();
        $last = ORM::factory(self::MAILER_MODEL)->where('sended', '=', 0)->count_all();
        $this->template->content->set(array(
            'total' => $total,
            'last' => $last,
            'stepcount' => self::MAILER_LIMIT,
            'admin_email' => $this->config['contact_email'],
            'letter' => $letter,
        ));
    }

    /**
     * Create lists of email
     * @throws Kohana_Exception
     */
    public function action_create(){
        $addtime = time() - Date::DAY * self::MAILER_DAYS_AGO;
        $user_table = ORM::factory('User')->table_name();
        $table = ORM::factory(self::MAILER_MODEL)->table_name();
        DB::delete( $table )->execute();

        $query = DB::select(DB::expr('DISTINCT(user_id) AS id, u.email AS email'))
            ->from(array('ads','a'))
            ->join(array($user_table,'u'),'INNER')->on('a.user_id','=','u.id')->on('u.no_mails','=', DB::expr(0))->on('u.email_verified','=', DB::expr(1))
            ->where('publish', '=', 1)
            ->and_where('addtime', '<=', $addtime);
        Database::instance()->query(Database::INSERT, 'INSERT INTO `ad_mailer` (`user_id`, `email`) '.$query);

        Flash::success('Очередь рассылки создана');
        $this->go('/admin/boardSender');
    }

    /**
     * Send another pack of letters
     * @throws Kohana_Exception
     */
    public function action_send(){
        $config = Kohana::$config->load('users');
        $table = ORM::factory(self::MAILER_MODEL)->table_name();
        $mails = DB::select('email', 'user_id')
            ->from($table)
            ->where('sended', '=', 0)
            ->limit(self::MAILER_LIMIT)
            ->execute();

        if(count($mails)){
            foreach($mails as $step=>$mail){
                if($step >= self::MAILER_LIMIT)
                    break;
                $this->_sendMailerLetter($mail['email'], $mail['user_id']);
                DB::update($table)->set(array('sended'=>1))->where('user_id', '=', $mail['user_id'])->execute();
            }
        }
        Flash::success('Отправлено писем: '. self::MAILER_LIMIT);
        $this->go('/admin/boardSender');
    }

    /**
     * Test letter sending
     */
    public function action_sendtest(){
        $mail = $this->config['contact_email'];
        $this->_sendMailerLetter($mail, $this->current_user->id);
        Flash::success('Тестовое письмо отправлено на '. $mail);
        $this->go('/admin/boardSender');
    }

    /**
     * Sending one letter to email
     * @param $mail
     * @throws Kohana_Exception
     * @throws View_Exception
     */
    protected function _sendMailerLetter($mail, $user_id){
        /* Get template */
        $addtime = time() - Date::DAY * self::MAILER_DAYS_AGO;
        $ads = ORM::factory('BoardAd')->where('user_id','=',$user_id)->and_where('addtime','<=', $addtime)->find_all();
        $template = View::factory(self::MAILER_LETTER_TEMPLATE)->set(array(
            'user_ads'=> array(self::MAILER_DAYS_AGO => $ads),
            'site_name'=> $this->config['project']['name'],
            'server_name'=> $_SERVER['HTTP_HOST'],
            'unsubscribe_link' => Model_User::generateCryptoLink('unsubscribe', $user_id),
        ));

        Email::instance()
            ->to($mail)
            ->from(KoMS::config()->robot_email)
            ->subject(KoMS::config()->project['name'] .': Ваше объявление давно не обновлялось')
            ->message($template->render(), true)->send();

    }
}
