<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Created by JetBrains PhpStorm.
 * User: butch
 * Date: 23.05.12
 * Time: 18:35
 * To change this template use File | Settings | File Templates.
 */
class Controller_Admin_BoardCategoryMove extends Controller_System_Admin
{
    public $skip_auto_render = array(
        'move',
    );

    public $skip_auto_content_apply = array(
        'index',
    );

    public function before(){
        parent::before();

        if($this->auto_render)
            $this->template->submenu= Widget::factory('adminBoardMenu')->render();
    }

    /**
     * List items
     */
    public function action_index(){
        /* Save category compares */
        if(isset($_POST['save'])){
            if(count($moveto = Arr::get($_POST, 'move_to')))
                foreach($moveto as $_category_id=>$_new_id)
                    DB::update(ORM::factory('BoardCategoryJB')->table_name())->value('new_id', $_new_id)->value('moved', 0)->where('id','=',$_category_id)->execute();
        }

        /* Move ads to new categories */
        if(isset($_POST['move'])){
            $new_parents = ORM::factory('BoardCategory')->where('parent_id', '>', 0)->find_all()->as_array('id', 'parent_id');
            $old_categories = ORM::factory('BoardCategoryJB')->where('new_id', '>', 0)->and_where('moved','=', 0)->order_by('new_id', 'ASC')->find_all()->as_array('id', 'new_id');
            foreach($old_categories as $_id=>$_new_id){
                DB::update(ORM::factory('BoardAd')->table_name())
                    ->where('category_id', '=', $_id)
                    ->set(array(
                        'category_id' => $_new_id,
                        'pcategory_id' => $new_parents[$_new_id],
                    ))
                ->execute();

                DB::update(ORM::factory('BoardCategoryJB')->table_name())->value('moved', 1)->where('id','=',$_id)
                ->execute();
            }
        }

        $this->template->scripts[] = "media/libs/bootstrap/js/bootbox.min.js";
        $this->template->scripts[] = "media/libs/bootstrap/js/bbox_".I18n::$lang.".js";
        $this->template->scripts[] = "media/js/admin/check_all.js";

        $categories = ORM::factory('BoardCategory')->getFullDepthArray();
        $categories_options = Arr::merge(array('0' => '..'), ORM::factory('BoardCategory')->getFullDepthArray());

        $old_categories = ORM::factory('BoardCategoryJB')->fulltree();

        $this->template->content = View::factory('admin/categories/board_category_compare')
            ->set('categories', $categories)
            ->set('categories_options', $categories_options)
            ->set('old_categories', $old_categories)
        ;
    }
}
