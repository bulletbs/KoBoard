<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Виджет "Меню админа"
 */
class Controller_Widgets_AdminBoardMenu extends Controller_System_Widgets {

    public $template = 'widgets/adminsubmenu';    // Шаблон виждета

    public function action_index()
    {
        $select = lcfirst(Request::initial()->controller());

        $menu = array(
            'Объявления' => array('board'),
            'Категории' => array('boardCategories'),
            'Фильтры' => array('boardFilters'),
            'Теги' => array('boardTags'),
            'Города' => array('boardCities'),
//            'JB категории' => array('boardCategoriesJB'),
            'Перенос' => array('boardCategoryMove'),
            'Жалобы' => array('boardAbuses'),
            'Рассылка' => array('boardSender'),
            'Модерация' => array('boardModerate'),
            'Стопслова' => array('boardModerateSw'),
        );

        // Вывод в шаблон
        $this->template->menu = $menu;
        $this->template->select = $select;
    }

}