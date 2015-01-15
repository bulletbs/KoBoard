<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Виджет "Меню админа"
 */
class Controller_Widgets_BoardCategoryMenu extends Controller_System_Widgets {

    public $template = 'widgets/board_category_menu';    // Шаблон виждета

    public function action_index()
    {
        $categories = Model_CatalogCategory::getCategoriesList();

        $this->template->set(array(
            'categories' => $categories,
            'active_alias' => Request::initial()->param('cat_alias'),
        ));
    }

}