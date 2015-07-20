<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Widgets_BoardSearch extends Controller_System_Widgets {
    public $json = array();
    public $skip_auto_content_apply = array(
        'cities',
        'filters',
        'sub_filter',
    );
    const REGION_LIST_CACHE = 'searchRegionRendered';
    const CITY_LIST_CACHE = 'searchCitiesRendered_';
    const CATEGORY_LIST_CACHE = 'searchCategoriesRendered';
    const FILTER_LIST_CACHE = 'searchFiltersRendered_';

    public $template = 'widgets/board_search_form';    // Шаблон виждета

    /**
     * Search form output
     */
    public function action_index()
    {
        $city_alias = Request::initial()->param('city_alias');
        $cat_alias = Request::initial()->param('cat_alias');
        $filter_alias = Request::initial()->param('filter_alias');
        if($filter_alias){
            $form_action = Route::get('board_subcat')->uri(array(
                'city_alias' => $city_alias,
                'cat_alias' => $cat_alias,
                'filter_alias' => $filter_alias,
            ));
        }
        else{
            $form_action = Route::get( $cat_alias ? 'board_cat' : 'board_city' )->uri(array(
                'city_alias' => $city_alias,
                'cat_alias' => $cat_alias,
            ));
        }

        /* CATEGORY NAME & FILTERS */
        $category_name = '';
        if($cat_alias){
            $category_id = Model_BoardCategory::getCategoryIdByAlias($cat_alias);
            $filters = Model_BoardFilter::loadFiltersByCategory($category_id, TRUE);
            Model_BoardFilter::loadSearchFilterValues($filters, Arr::get($_GET, 'filters', array()));
            $filters_view = View::factory('widgets/_board_filters_search_list', array(
                'filters' => $filters,
            ))->render();
            if($category_id)
                $category_name = Model_BoardCategory::getField('name', $category_id);
        }
        if(!$price_filter = Arr::get($_GET, 'price'))
            $price_filter = array('from'=>NULL, 'to'=>NULL);

        /* REGION NAME */
        $region_name = '';
        if($city_alias){
            $region_id = Model_BoardCity::getCityIdByAlias($city_alias);
            if($region_id)
                $region_name = Model_BoardCity::getField('name', $region_id);
        }

        /* BOARD CONFIG */
        $cfg = Kohana::$config->load('board')->as_array();

        $this->template->set(array(
            'form_action' => $form_action,

            'region_name' =>  $region_name,
            'region_ailas' => $city_alias,
            'city_list' => $this->_regionListRender(),

            'category_name' => $category_name,
            'category_alias' => $cat_alias,
            'category_list' => $this->_categoryListRender(),
            'filters' => isset($filters_view) ? $filters_view : '',

            'price_filter' => $price_filter,
            'board_cfg' => $cfg,
            'is_job_category' => isset($category_id) && in_array($category_id, Model_BoardCategory::getJobIds()),
        ));
    }

    /**
     * Get cities list
     */
    public function action_cities(){
        $region = (int) Request::current()->post('region_id');
        if(!$this->request->is_ajax() || !$region)
            return NULL;

        $region = ORM::factory('BoardCity', $region);
        if($region->loaded() && NULL === ($this->json['content'] = Cache::instance()->get(self::CITY_LIST_CACHE . $region->id))){
            $cities = DB::select('id', 'alias', 'parent_id', 'name')->from(ORM::factory('BoardCity')->table_name())->where('lvl','=',2)->where('parent_id','=',$region->id)->order_by('name')->as_assoc()->execute();
            $template = View::factory('widgets/_board_cities_search_list')->set(array(
                'cities' => $cities,
                'region' => $region,
            ));
            $this->json['content'] = $template->render();
            Cache::instance()->set(self::CITY_LIST_CACHE . $region->id, $this->json['content'], Date::DAY*365);
        }
        echo json_encode($this->json);
    }

    /**
     * Get filters and render filters list
     * @return null
     * @throws Cache_Exception
     * @throws View_Exception
     */
    public function action_filters(){
        $category = (int) Request::current()->post('category_id');
        if(!$this->request->is_ajax() || !$category)
            return NULL;

        $category = ORM::factory('BoardCategory', $category);
        if($category->loaded() && NULL === ($this->json['content'] = Cache::instance()->get(self::FILTER_LIST_CACHE. $category->id))){
            $filters = Model_BoardFilter::loadFiltersByCategory($category->id);
            $template = View::factory('widgets/_board_filters_list')->set(array(
                'filters' => $filters,
                'category' => $category,
            ));
            $this->json['content'] = $template->render();
            Cache::instance()->set(self::FILTER_LIST_CACHE . $category->id, $this->json['content'], Date::HOUR*24);
        }
        echo json_encode($this->json);
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

        if($id && $parent && $value){
            $parameters = array(
                'data-id' => $id,
                'data-parent' => $parent,
            );
            $options = Model_BoardFilter::loadSubFilterOptions($id, $value, TRUE);
            if(!count($options))
                $parameters['disabled'] = 'disabled';
            $this->json['content'] = Form::select('filters['.$id.']', $options, $value, $parameters);
            $this->json['status'] = TRUE;
        }
        echo json_encode($this->json);
    }

    /**
     * Region list renderer
     * @return mixed|string
     */
    protected function _regionListRender(){
        if(NULL === ($content = Cache::instance()->get(self::REGION_LIST_CACHE))){
            $regions = DB::select('id', 'alias', 'name')->from(ORM::factory('BoardCity')->table_name())->where('lvl','=',1)->order_by('name')->as_assoc()->execute();

            $template = View::factory('widgets/_board_region_search_list')->set(array(
                'regions' => $regions,
                'all_uri'  => URL::base() . Route::get('board_city')->uri(),
            ));
            $content = $template->render();
            Cache::instance()->set(self::REGION_LIST_CACHE, $content, Date::HOUR*24);
        }
        return $content;
    }

    /**
     * Categories lists renderer
     * @return mixed|string
     */
    protected function _categoryListRender(){
        if(NULL === ($content = Cache::instance()->get(self::CATEGORY_LIST_CACHE))){
            $result = DB::select('id', 'alias', 'name')->from(ORM::factory('BoardCategory')->table_name())->where('lvl','=',1)->order_by('name')->as_assoc()->execute();
            $categories = array();
            foreach($result as $category){
                $category['link'] = URL::base() . Route::get('board_cat')->uri(array(
                    'cat_alias' => $category['alias'],
                ));
                $categories[] = $category;
            }
            $result = DB::select('id', 'alias', 'parent_id', 'name')->from(ORM::factory('BoardCategory')->table_name())->where('lvl','=',2)->order_by('name')->as_assoc()->execute();
            $subcats = array();
            foreach($result as $res){
                $subcats[$res['parent_id']][] = $res;
            }
            $template = View::factory('widgets/_board_category_search_list')->set(array(
                'categories'  => $categories,
                'subcats'  => $subcats,
                'all_uri'  => Route::get('board_cat')->uri(),
            ));
            $content = $template->render();
            Cache::instance()->set(self::CATEGORY_LIST_CACHE, $content, Date::HOUR*24);
        }
        return $content;
    }

    protected function _filterListRender(){

    }
}