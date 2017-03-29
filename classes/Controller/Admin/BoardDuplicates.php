<?php defined('SYSPATH') or die('No direct script access.');

/**
 * CRUD Controller
 * Have an Actions to operate ORM models
 */
class Controller_Admin_BoardDuplicates extends Controller_System_Admin{

    const MODELNAME = 'Model_BoardAd';

    public $skip_auto_content_apply = array(
        'index',
        'email',
        'text',
        'top100',
        'auto',
    );

    public $skip_auto_render = array(
        'clearall',
        'delall',
    );

    protected $_pagination;
    protected $_select_type = 'title';
    protected $_distinct_value = 'title';

    protected $_top100_field = 'title';
    protected $_top100_where = 'email';
    protected $_top100_value;
    protected $_top100_subvalue;

    public function before(){
        parent::before();

        $this->_user_uri = 'admin/users';
        $this->_crud_uri = 'admin/board';

        /* Rendering submenu if widget name exists */
        if($this->auto_render)
            $this->template->submenu= Widget::factory('adminBoardMenu')->render();

        $this->scripts[] = "media/libs/bootstrap/js/bootbox.min.js";
        $this->scripts[] = "media/libs/bootstrap/js/bbox_".I18n::$lang.".js";
//        $this->scripts[] = "media/js/admin/check_all.js";
    }

    /**
     * List items
     */
    public function action_index(){
        $this->template->content = $this->_setIndexTemplate();
    }

    public function action_email(){
        $this->_setType('email');
        $this->template->content = $this->_setIndexTemplate();
    }
    public function action_text(){
        $this->_setType('text');
        $this->template->content = $this->_setIndexTemplate();
    }

    /**
     * TOP 100
     * @throws Kohana_Exception
     */
    public function action_top100(){
        $this->_setType($_GET['type']);
        $this->_setTopValue();

        /* Delete duplicates */
        $delete = Arr::get($_GET, 'delete', false);
        if($delete){
            $deleted = 0;
            $all = Arr::get($_GET, 'all', false);
            $items = $this->_generateDeleteSql($all)->as_object(self::MODELNAME)->execute()->as_array('id');
//            die(Debug::vars(count($items)));
            foreach($items as $item){
                $item->delete();
                $deleted++;
            }
            Flash::success('Удалено '.$deleted.' дубликатов');
            $this->redirect( Request::current()->referrer() );
        }
        $this->template->content = $this->_setTopTemplate();
    }

    /**
     * Очистить все совпадения от дублей
     * @throws Kohana_Exception
     */
    public function action_clearall(){
        $all = Arr::get($_GET, 'all', 0);
        $this->_setType($_GET['type']);
        $this->_setTopValue();
        $list = $this->_generateTopSql()
            ->having('cnt', '>', $all?'0':'1')
            ->limit($all?1:1000)
            ->offset(0)
            ->execute();
        foreach($list as $row){
            $_GET['subvalue'] = $row['val'];
            $this->_setTopValue(false);
            $items = $this->_generateDeleteSql($all)->as_object(self::MODELNAME)->execute()->as_array('id');
            $deleted = 0;
            foreach($items as $item){
                $item->delete();
                unset($item);
                $deleted++;
            }
            Flash::success('Удалено '.$deleted.' дубликатов'.(!$all ? ' от пользователя ID='.$row['val'] : ''));
            unset($items);
        }
        $this->redirect( Request::current()->referrer() );
    }

    /**
     * Автоматическая обработка раздела
     * @throws Kohana_Exception
     */
    public function action_auto(){
        $this->_setType($_GET['type']);
        $variant = $this->_generateAutoSql()->execute();
        $_GET['value'] = $variant[0]['subtext'];
        $this->_setTopValue(false);
        $list = $this->_generateTopSql()
            ->having('cnt','>','1')
            ->limit(1000)
            ->offset(0)
            ->execute();
        foreach($list as $row){
            $_GET['subvalue'] = $row['val'];
            $this->_setTopValue(false);
            $items = $this->_generateDeleteSql()->as_object(self::MODELNAME)->execute()->as_array('id');
            $deleted = 0;
            foreach($items as $item){
                $item->delete();
                unset($item);
                $deleted++;
            }
            Flash::success('Удалено '.$deleted.' дубликатов c '. ($this->_select_type=='title'?'заголовком ':'текстом') .' &#171;'.$this->_top100_value.'&#187; от пользователя ID='.$row['val']);
            unset($items);
        }
        $this->template->content = View::factory('admin/duplicates/auto', array(
            'type' => $this->_select_type,
            'route' => Route::get('admin'),
            'route_params' => array(
                'controller' => $this->request->controller(),
            ),
        ));
    }

    /**
     * Установить тип выборки
     * @param $type
     * @throws Kohana_Exception
     */
    protected function _setType($type){
        $this->_select_type = $type;
        switch($type){
            case 'title':
                $this->_distinct_value = 'title';
                $this->_top100_field = 'user_id';
                $this->_top100_where = 'title';
                break;
            case 'email':
                $this->_distinct_value = 'users.email';
                $this->_top100_field = 'title';
                $this->_top100_where = 'email';
                break;
            case 'text':
                $this->_distinct_value = 'SUBSTRING(description, 1, 200)';
                $this->_top100_field = 'user_id';
                $this->_top100_where = 'SUBSTRING(description, 1, 200)';
                break;
            default:
                throw new Kohana_Exception('Не указан тип выборки');
                break;

        }
    }

    /**
     * Setup TOP100 values
     * @param bool $decode
     * @throws Kohana_Exception
     */
    protected function _setTopValue($decode = true){
        $value = Arr::get($_GET, 'value', false);
        if(!$value)
            throw new Kohana_Exception('Не указано значение для выборки');
//        $this->_top100_value = str_replace(array("\n","\r\r\n"), "\r\n", $value);
//        $this->_top100_subvalue = str_replace(array("\n","\r\r\n"), "\r\n", Arr::get($_GET, 'subvalue'));
        $this->_top100_value = $decode ? rawurldecode($value) : $value;
        $this->_top100_subvalue = $decode ? rawurldecode(Arr::get($_GET, 'subvalue')) : Arr::get($_GET, 'subvalue');
    }

    /**
     * Установка и наполнение шаблона заглавной страницы
     * @return View
     */
    protected function _setIndexTemplate(){
        $this->_generatePagination();
        $items = $this->_generateIndexSql()->execute();
//        $items = array(0=>array('subtext'=>'sddlk sd;gnk sd;lfngsd f;gkndg lknsd;fgknsd;flnsd;fln','cnt'=>'122'));

        return View::factory('admin/duplicates/index', array(
            'type' => $this->_select_type,
            'pagination' => clone($this->_pagination),
            'items' => $items,
            'route' => Route::get('admin'),
            'route_params' => array(
                'controller' => $this->request->controller(),
            ),
        ));
    }

    /**
     * Установка и наполнение шаблона TOP100
     * @return View
     */
    protected function _setTopTemplate(){
        $this->_generatePagination();
        $items = $this->_generateTopSql()->execute()->as_array();
//        $items = array(0=>array('subtext'=>'sddlk sd;gnk sd;lfngsd f;gkndg lknsd;fgknsd;flnsd;fln','cnt'=>'122'));

        /* Список пользователей для замены ID */
        $users = array(0 => 'без email');
        if($this->_select_type != 'email' && count($items))
            $users += DB::select('id', 'email')->from('users')->where('id','IN',array_map(function($n){return $n['val'];}, $items))->execute()->as_array('id', 'email');

        return View::factory('admin/duplicates/top100', array(
            'type' => $this->_select_type,
            'value' => $this->_top100_value,
            'pagination' => clone($this->_pagination),
            'items' => $items,
            'users' => $users,
            'route' => Route::get('admin'),
            'route_params' => array(
                'controller' => $this->request->controller(),
            ),
        ));
    }

    /**
     * Создание запроса для автоматической очистки
     * @return Database_Query_Builder_Select
     */
    protected function _generateAutoSql(){
        $query = DB::select(array(DB::expr('DISTINCT(CONCAT('.$this->_distinct_value.',"|",user_id))'), 'uniqtext'), array(DB::expr($this->_distinct_value),'subtext'), array(DB::expr('count(ads.id)'),'cnt'))
            ->from('ads')
            ->group_by('uniqtext')
            ->having('cnt','>',1)
            ->order_by('cnt','DESC')
            ->limit(1);
//        die($query);
        return $query;
    }

    /**
     * Создание запроса заглавной страницы
     * @return Database_Query_Builder_Select
     */
    protected function _generateIndexSql(){
        $query = DB::select(array(DB::expr('DISTINCT('.$this->_distinct_value.')'), 'subtext'), array(DB::expr('count(ads.id)'),'cnt'))
            ->from('ads')
            ->group_by('subtext')
            ->having('cnt','>',1)
            ->order_by('cnt','DESC')
            ->limit($this->_pagination->items_per_page)
            ->offset($this->_pagination->offset);
        if($this->_select_type == 'email'){
            $query->join('users','INNER')->on('users.id','=','ads.user_id');
            $query->where('users.email','<>','');
            $query->select('ads.user_id');
        }
        return $query;
    }

    /**
     * Создание запроса TOP100
     * @return Database_Query_Builder_Select
     */
    protected function _generateTopSql(){
        $query = DB::select(array(DB::expr('DISTINCT('.$this->_top100_field.')'), 'val'), array(DB::expr('count(ads.id)'),'cnt'))
            ->from('ads')
            ->where(DB::expr($this->_top100_where),'=',$this->_top100_value)
            ->group_by('val')
            ->order_by('cnt','DESC')
            ->limit($this->_pagination->items_per_page)
            ->offset($this->_pagination->offset);
//        die($query);
        return $query;
    }


    /**
     * Создание запроса TOP100
     * @param bool $all
     * @return Database_Query_Builder_Select
     */
    protected function _generateDeleteSql($all = false){
        $query = DB::select('*')
            ->from('ads')
            ->where(DB::expr($this->_top100_where), '=', $this->_top100_value);
        if(!$all)
            $query->and_where(DB::expr($this->_top100_field), '=', $this->_top100_subvalue);
        $query->order_by('addtime','DESC')
            ->limit(10000)
            ->offset($all ? 0 : 1);
        return $query;
    }

    /**
     * Создание постранички
     */
    protected function _generatePagination($items_per_page = 50, $total_items=500){
        $this->_pagination = Pagination::factory(
            array(
                'total_items' => $total_items,
                'group' => 'admin_float',
                'items_per_page' => $items_per_page,
            ))
            ->route_params(array(
                'controller' => $this->request->controller(),
                'action' => $this->request->action(),
            ));
    }
}