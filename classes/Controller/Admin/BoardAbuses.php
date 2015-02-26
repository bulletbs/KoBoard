<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Created by JetBrains PhpStorm.
 * User: butch
 * Date: 23.05.12
 * Time: 18:35
 * To change this template use File | Settings | File Templates.
 */
class Controller_Admin_BoardAbuses extends Controller_System_Admin
{
    public $skip_auto_render = array(
        'delete',
        'multi',
        'delall',
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
        $this->uri = '';

        $this->template->scripts[] = "media/libs/bootstrap/js/bootbox.min.js";
        $this->template->scripts[] = "media/libs/bootstrap/js/bbox_".I18n::$lang.".js";
        $this->template->scripts[] = "media/js/admin/check_all.js";

        $orm = ORM::factory('BoardAbuse');
        $count = $orm->count_all();
        $pagination = Pagination::factory(
            array(
                'total_items' => $count,
                'group' => 'admin',
            )
        )->route_params(
            array(
                'controller' => Request::current()->controller(),
            )
        );

        /**
         * @var $abuse ORM
         */
        $orm = ORM::factory('BoardAbuse')
            ->with('ad')
            ->limit($pagination->items_per_page)
            ->offset($pagination->offset);
        $abuses = $orm->find_all();
        $this->template->content = View::factory('admin/abuses/index')
            ->set('pagination', $pagination)
            ->set('abuses', $abuses)
        ;
    }

    /**
     * Delete item
     */
    public function action_delete(){
        $comment = ORM::factory('BoardAbuse', $this->request->param('id'));
        if($comment->loaded())
            $comment->delete();
        $this->redirect('admin/boardAbuses' . URL::query());
    }

    /**
     * Delete all abuses
     */
    public function action_delall(){
        $count = DB::delete( ORM::factory('BoardAbuse')->table_name() )->execute();
        Flash::success(__('All abuses (:count) was successfully deleted', array(':count'=>count($count) )));
        $this->redirect('admin/boardAbuses' . URL::query());
    }


    /**
     * Multi action related to button
     */
    public function action_multi(){
        $ids = Arr::get($_POST, 'operate');
        if(isset($_POST['delete_all']) && count($ids)){
            $count = DB::delete( ORM::factory('BoardAbuse')->table_name() )->where('id', 'IN', $ids)->execute();
            Flash::success(__('All abuses (:count) was successfully deleted', array(':count'=>count($count) )));
        }
        $this->redirect('admin/boardAbuses' . URL::query());

    }
}