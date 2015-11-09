<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Widgets_BoardSearchCategoryTree extends Controller_System_Widgets {
    public $auto_render = FALSE;

    const TREE_CACHE = 'searchCategoryTree_';

    public $template = 'widgets/board_search_category_tree';    // Шаблон виждета

    /**
     * Search form output
     */
    public function action_index()
    {
        $city_alias = Request::initial()->param('city_alias');
        $city_id = Model_BoardCity::getCityIdByAlias( $city_alias );

        if(NULL === ($content = Cache::instance()->get(self::TREE_CACHE . $city_id))){
            $categories = Model_BoardCategory::getCategoriesTree();
            $this->template->set(array(
                'categories' => $categories,
                'city_alias' => $city_alias,
            ));
        }
        else
            $this->template = $content;
    }
}