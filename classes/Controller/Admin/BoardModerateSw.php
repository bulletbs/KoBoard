<?php defined('SYSPATH') or die('No direct script access.');

/**
 * CRUD Controller
 * Have an Actions to operate ORM models
 */
class Controller_Admin_BoardModerateSw extends Controller_Admin_BoardModerate{

    public $moderate_field = 'stopword';

    /**
     * List items
     */
    public function action_index(){
        $this->template->scripts[] = "media/libs/bootstrap/js/bootbox.min.js";
        $this->template->scripts[] = "media/libs/bootstrap/js/bbox_".I18n::$lang.".js";
        $this->template->scripts[] = "media/js/admin/check_all.js";

        $orm = ORM::factory($this->model_name);
        $orm->where($this->moderate_field,'=',self::NOT_MODERATED);
        $orm->and_where('stopword', '=', 1)
            ->and_where('key','=','');
        $count = $orm->count_all();
        $pagination = Pagination::factory(
            array(
                'total_items' => $count,
                'group' => 'admin_float',
            )
        )->route_params(
                array(
                    'controller' => Request::current()->controller(),
                )
            );
        /**
         * @var $comment ORM
         */
        $orm = ORM::factory($this->model_name)
            ->where($this->moderate_field,'=',self::NOT_MODERATED)
            ->and_where('stopword', '=', 1)
            ->and_where('key','=','')
            ->limit($pagination->items_per_page)
            ->offset($pagination->offset)
            ->order_by('addtime', 'DESC');
        $items = $orm->find_all();
        $photos = Model_BoardAdphoto::adsFullPhotoList($items->as_array('id','id'));
        $this->template->content = View::factory('admin/board/moderatesw')
            ->set('pagination', $pagination)
            ->set('items', $items)
            ->set('photos', $photos)

            ->set('list_fields',$this->list_fields)
            ->set('crud_uri',$this->_crud_uri)
            ->set('user_uri',$this->_user_uri)
            ->set('moderate_uri',$this->_moderate_uri)
            ->set('moderate_name',$this->_moderate_name)
            ->set('moderate_field',$this->moderate_field)
            ->set('item_name',$this->_item_name)
            ->set('labels',$this->_getModelLabels())
        ;
    }
}