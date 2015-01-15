<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Board extends Controller_Admin_Crud
{
    public $submenu = 'adminBoardMenu';

    protected $_item_name = 'ad';
    protected $_crud_name = 'Site Ads';

    protected $_model_name = 'BoardAd';

    public $list_fields = array(
        'id',
        'title',
        'addTime',
        'price',
    );

    /**
     * Actions with manual rendering
     * @var array
     */
    public $skip_auto_content_apply = array(
        'add',
        'edit',
        'import',
        'test',
        'partitions',
    );


    protected $_sort_fields = array(
        'category_id' => array(
            'label' => 'Показать категорию',
            'type' => 'select',
            'oper'=>'IN',
        ),
        'title' => array(
            'label' => 'Найти',
            'type'=>'text',
            'oper'=>'like',
        ),
        'id' => array(
            'label' => 'ID',
            'type'=>'text',
            'oper'=>'=',
        ),
    );


    public function action_index(){
        /* Filter Parent_id initialize  */
        $this->_sort_fields['category_id']['data']['options'][0] = 'Все категории';
        foreach(ORM::factory('BoardCategory')->fulltree() as $item)
            $this->_sort_fields['category_id']['data']['options'][$item->id] = $item->getLeveledName(0);
        /* category */
        if(!isset($this->_sort_values['category_id']))
            $this->_sort_values['category_id'] = 0;
        $this->_sort_fields['category_id']['data']['selected'] = $this->_sort_values['category_id'];
        if(isset($this->_sort_values['category_id']))
            $this->_sort_values['category_id'] = ORM::factory('BoardCategory', $this->_sort_values['category_id'])->descendants(TRUE)->as_array('id', 'id');

        $this->_sort_fields['title']['data'] = $this->_sort_values['title'];
        $this->_sort_fields['id']['data'] = $this->_sort_values['id'];

        parent::action_index();
    }

    public $_form_fields = array(
        'title' => array('type'=>'text'),
        'category_id' => array('type'=>'select','data'=>array('options'=>array())),
        'filters' => array(
            'type'=>'call_view',
            'data'=>'admin/board/filter_options'
        ),
        'type' => array('type'=>'select'),
        'business' => array('type'=>'checkbox'),
        'user_id' => array('type'=>'select'),
        'city_id' => array('type'=>'select'),
        'price' => array('type'=>'text'),
//        'description' => array('type'=>'editor'),
        'description' => array('type'=>'textarea'),
        'name'=>array('type'=>'text'),
        'email'=>array('type'=>'text'),
        'phone'=>array('type'=>'text'),
        'address'=>array('type'=>'text'),
//        'photo' => array('type'=>'file'),
        'photo' => array(
            'type'=>'call_view',
            'data'=>'admin/board/photos',
            'advanced_data'=>array(
                'photos'=>array(),
            )
        ),
    );

    public $form_fields_save_extra = array();

    /**
     * Form preloader
     * 1. load form JS file
     * 2. load categories
     * 3. load photos
     *
     * @param $model
     * @param array $data
     * @return array|bool|void
     */
    protected function _processForm($model, $data = array()){
        $this->scripts[] = 'assets/board/js/admin/board_filter_options.js';

        /* Setting categories select field */
        $this->_form_fields['type']['data']['options'] = array_map('__', Model_BoardAd::$adType);

        /* Category List */
        $this->_form_fields['category_id']['data']['options'] = ORM::factory('BoardCategory')->getFullDepthArray();

        /* Filters List */
        $this->_form_fields['filters']['advanced_data']['preloaded'] = $this->_render_filters_list($this->_form_fields['category_id']['data']['selected'], $model->id);

        /* Municipals list */
        $this->_form_fields['city_id']['data']['options'] = ORM::factory('BoardCity')->getTwoLevelArray();

        /* Users list */
        $this->_form_fields['user_id']['data']['options'] = ORM::factory('Profile')->find_all()->as_array('user_id', 'name');

        /* Setting photos field */
        $this->_form_fields['photo']['advanced_data']['photos'] = $model->photos->find_all()->as_array('id');

        parent::_processForm($model);
    }

    /**
     * Saving Model Method
     * @param $model
     */
    protected function _saveModel($model){
        parent::_saveModel($model);

        /* Save photos */
        $files = Arr::get($_FILES, 'photos', array('tmp_name' => array()));
        foreach($files['tmp_name'] as $k=>$file){
            $model->addPhoto($file);
        }

        /* Deleting photos */
        $files = Arr::get($_POST, 'delphotos', array());
        foreach($files as $file_id){
            $photo = ORM::factory('BoardAdphoto', $file_id)
                ->where('ad_id','=',$model->id)
                ->delete();
        }

        /* Setting up main photo */
        if(!isset($setmain))
            $setmain = Arr::get($_POST, 'setmain');
        $model->setMainPhoto($setmain);

        /* Save filters */
        $filters = Arr::get($_POST, 'filters', array());
        $model->saveFilters($filters);
    }

    /**
     * Loading model to render form
     * @param null $id
     * @return ORM
     */
    protected function _loadModel($id = NULL){
        $model = ORM::factory($this->_model_name, $id)->with('options');
//        $this->_form_fields['photo']['data'] = $model->getThumb();

        /* Buy/sell list */
        $this->_form_fields['type']['data']['selected'] = $model->type;

        /* values */
        $this->_form_fields['category_id']['data']['selected'] = $model->category->id;
        $this->_form_fields['city_id']['data']['selected'] = $model->city->id;
        $this->_form_fields['user_id']['data']['selected'] = $model->user->id;

        return $model;
    }


    public function action_import(){
        set_time_limit(300);
        $result = DB::select(DB::expr('max(id) max'))->from('ads')->execute();
//        echo Debug::vars($result[0]['max']);
        $result = DB::select()->from('jb_board')->where('id','>',$result[0]['max'])->limit(50000)->as_assoc()->execute();
        foreach($result as $row){
            Model_BoardAd::import_ad($row);
        }
    }

    public function action_test(){
        set_time_limit(300);
        $categories = ORM::factory('BoardCategory')->find_all()->as_array('id');
        $cities = ORM::factory('BoardCity')->find_all()->as_array('id');
        $result = DB::select(
            'id',
            'city_id',
            'category_id'
        )->from('ads')->where('pcity_id','=','0')->limit(100000)->as_assoc()->execute();
        foreach($result as $row){
            if(isset($categories[$row['category_id']]) && isset($cities[$row['city_id']])){
                $values = array(
                    'pcategory_id' => $categories[$row['category_id']]->parent_id,
                    'pcity_id' => $cities[$row['city_id']]->parent_id,
                );
                DB::update('ads')->set($values)->where('id','=', $row['id'])->execute();
            }
            else{
                DB::delete('ads')->where('id','=', $row['id'])->execute();
            }
        }
        echo count($result);
    }

    public function action_partitions(){
        $subcats = array();
        $pcategories = ORM::factory('BoardCategory')->where('lvl','=','1')->find_all()->as_array('id');
        $categories = ORM::factory('BoardCategory')->where('lvl','=','2')->find_all()->as_array('id');
        foreach($categories as $category){
            $subcats[$category->parent_id][] = $category->id;
        }

        echo "PARTITION BY LIST(category_id) (";
        foreach($subcats as $pcat=>$subcat){
            echo 'PARTITION '. $pcategories[$pcat]->alias .' VALUES IN ( '. implode(', ',$subcat)." ),<br>";
        }
        echo ")";
    }


    /**
     * Get Type Filter lists (AJAX)
     */
    public function action_get_filters(){
        $this->json['status'] = TRUE;
        $post = Arr::extract($_POST, array('model_id','category_id'));
        $this->json['filters'] = $this->_render_filters_list($post['category_id'], $post['model_id']);
    }

    /**
     * Load subfilters
     */
    public function action_sub_filter(){
        if(!$this->request->is_ajax() && $this->request->initial())
            $this->go(Route::get('board')->uri());
        $id = $this->request->param('id');
        $parent = Arr::get($_POST, 'parent');
        $value = Arr::get($_POST, 'value');
        if($id && $parent && $value)
            $this->json['content'] = $this->_render_sub_filter($id, $parent, $value);
        if(!empty($this->json['content']))
            $this->json['status'] = TRUE;
    }

    /**
     * Загрузить список выпадающих фильтров
     * @param int $category
     * @param integer $model_id
     * @return bool|string
     */
    protected function _render_filters_list($category, $model_id = NULL){
        $filters = Model_BoardFilter::loadFiltersByCategory($category);
        $values = Arr::get($_POST, 'filters', array());
        Model_BoardFilter::loadFilterValues($filters, $values, $model_id);

        return View::factory('admin/board/filters', array('filters' => $filters))->render();
    }

    /**
     * Rendering sub filter options list (like Models of Mark)
     * @param int $id - sub filter ID
     * @param int $parent_id - Parent filter ID
     * @param int $parent_value - value of parent filter
     * @param int|null $selected - current filter value
     * @return string
     */
    protected function _render_sub_filter($id, $parent_id, $parent_value, $selected = NULL){
        /* Load options related to selected parent option */
        $content = NULL;
        $options = ORM::factory('BoardOption')->where('filter_id','=',$id)->and_where('parent_id', '=', $parent_value)->order_by('value', 'ASC')->find_all()->as_array('id', 'value');
        if($id && $parent_id && $parent_value){
            $parameters = array(
                'data-id' => $id,
                'data-parent' => $parent_id,
            );
            if(!count($options))
                $parameters['disabled'] = 'disabled';
            $content = Form::select('filters['.$id.']', $options, $selected, $parameters);
        }
        return $content;
    }
}
