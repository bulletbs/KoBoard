<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board sender
 */
class Task_BoardSender extends Minion_Task
{
	CONST MAILER_MODEL = 'BoardMailer';
	CONST MAILER_LETTER_TEMPLATE = 'board/mail/ad_refresh_reminder';
	CONST MAILER_DAYS_AGO = 30;

    public $sended = 0;
	public $mailer_limit = 5000;

    protected function _execute(Array $params){
        $start = time();
        Kohana::$environment = !isset($_SERVER['windir']) && !isset($_SERVER['GNOME_DESKTOP_SESSION_ID']) ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;
        $_SERVER['SERVER_NAME'] = KoMSConfig::instance()->project['host'];
	    set_time_limit(600);

	    if(BoardConfig::instance()->mailer_queue_step)
		    $this->mailer_limit = BoardConfig::instance()->mailer_queue_step;

	    $table = ORM::factory(self::MAILER_MODEL)->table_name();
	    $mails = DB::select('email', 'user_id')
	               ->from($table)
	               ->where('sended', '=', 0)
	               ->limit($this->mailer_limit)
	               ->execute();

	    if(count($mails)){
		    foreach($mails as $step=>$mail){
//			    $this->_sendMailerLetter('bulletua@gmail.com', $mail['user_id']);
//			    die();
			    $this->_sendMailerLetter($mail['email'], $mail['user_id']);
			    DB::update($table)->set(array('sended'=>1))->where('user_id', '=', $mail['user_id'])->execute();
		    }
	    }
        print 'Operation taken '. (time() - $start) .' seconds'.PHP_EOL;
        print 'Total '. $this->sended .' notification sended '.PHP_EOL;
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
			'site_name'=> KoMS::config()->project['name'],
			'server_name'=> URL::base(KoMS::protocol()),
			'unsubscribe_link' => URL::site(Model_User::generateCryptoLink('unsubscribe', $user_id), KoMS::protocol()),
		));

		Email::instance()
		     ->reset()
		     ->to($mail)
		     ->from(KoMS::config()->robot_email)
		     ->subject(KoMS::config()->project['name'] .': Ваше объявление давно не обновлялось')
		     ->message($template->render(), true)->send();
		$this->sended++;
	}
}