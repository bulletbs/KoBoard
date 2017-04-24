<?php defined('SYSPATH') or die('No direct script access.');

class Model_BoardMailer extends ORM
{
    protected $_table_name = 'ad_mailer';


    public function labels(){
        return array(
            'id'=>'ID',
            'user_id'=>'Пользователь',
            'email'=>'Почтовый адрес',
            'sended'=>'Отправлено',
        );
    }

    public static function createMailerList(){}
    public static function buildQueue(){}
}