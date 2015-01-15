<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Widgets_BoardCityMenu extends Controller_System_Widgets {

    public $template = 'widgets/board_city_menu';    // Шаблон виждета

    public function action_index()
    {
        $cities = ORM::factory('BoardCity')->where('lvl', '=', 1)->order_by('name')->find_all();
        $this->template->set(array(
            'cities' => $cities,
//            'active_alias' => Request::initial()->param('cat_alias'),
        ));
    }

}