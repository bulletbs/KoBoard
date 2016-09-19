<?php defined('SYSPATH') or die('No direct script access.');

class Model_BoardNotice extends ORM
{
    CONST ICON = 'icon-exclamation-sign';

    protected $_table_name = 'user_notice';

    public static $types = array(
        0 => 'нарушение правил',
        1 => 'удаление объявления',
        2 => 'удаление магазина',
    );

    public function labels(){
        return array(
            'id'=>'ID',
            'user_id'=>'Кому',
            'type'=>'Тип',
            'text'=>'Текст оповещения',
            'sendtime'=>'Отправлено',
            'received'=>'Получено',
        );
    }

    /**
     * Create new Notice
     * @param $user_id
     * @param $type
     * @param $text
     */
    public static function addNotice($user_id, $type, $text){
        ORM::factory('BoardNotice')->values(array(
            'user_id' => $user_id,
            'type' => $type,
            'text' => $text,
        ))->save();
    }

    /**
     * Look for unreaded notices count
     * @param $user_id
     * @return int
     */
    public static function lookForNotice($user_id){
        $unreaded = ORM::factory('BoardNotice')
            ->where('user_id', '=', $user_id)
            ->where('received', '=', 0)
            ->count_all();
        return $unreaded;
    }
}