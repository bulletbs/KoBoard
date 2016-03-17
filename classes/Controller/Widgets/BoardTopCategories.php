<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Widgets_BoardTopCategories extends Controller_System_Widgets {

    const TOP_CATEGORIES_CACHE = 'BoardTopCategories_';

    public $skip_auto_content_apply = array(
        'index',
    );

    public $template = 'widgets/board_top_categories';    // Шаблон виждета

    /**
     * Search form output
     */
    public function action_index()
    {
        $region = Request::initial()->param('city_alias');
        if(FALSE === ($this->template = Cache::instance()->get(self::TOP_CATEGORIES_CACHE . $region, FALSE))){
            $this->template = View::factory('widgets/board_top_categories');
            $this->template->set(array(
                'route' => Route::get('board_cat'),
                'region' => $region,
            ));
            Cache::instance()->set(self::TOP_CATEGORIES_CACHE . $region, $this->template->render(), Date::MONTH);
        }
        $this->response->body($this->template);
    }
}