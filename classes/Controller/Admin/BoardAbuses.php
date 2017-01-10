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
        'remove',
        'delete',
        'multi',
        'delall',
        'delwads',
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

        $this->scripts[] = "media/libs/bootstrap/js/bootbox.min.js";
        $this->scripts[] = "media/libs/bootstrap/js/bbox_".I18n::$lang.".js";
        $this->scripts[] = "media/js/admin/check_all.js";

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
        $abuse = ORM::factory('BoardAbuse', $this->request->param('id'));
        if($abuse->loaded())
            $abuse->delete();
        $this->redirect('admin/boardAbuses' . URL::query());
    }

    /**
     * Delete item with ad
     */
    public function action_remove(){
        $abuse = ORM::factory('BoardAbuse', $this->request->param('id'));
        if($abuse->loaded()){
            if($abuse->ad->loaded())
                $abuse->ad->delete();
            $abuse->delete();
        }
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
     * Delete all abuses
     */
    public function action_delwads(){
        $ids = DB::select( 'ad_id' )->from(ORM::factory('BoardAbuse')->table_name())->execute()->as_array();
        $ids = array_map(function($n){return $n['ad_id'];}, $ids);
        $ads = ORM::factory('BoardAd')->where('id','IN',$ids)->find_all();
        foreach($ads as $_ad)
            $_ad->delete();
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
