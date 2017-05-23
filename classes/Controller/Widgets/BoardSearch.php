<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Widgets_BoardSearch extends Controller_System_Widgets {
    public $json = array(
        'status'=>true,
    );
    public $skip_auto_content_apply = array(
        'regions',
        'cities',
        'parts',
        'categories',
        'filters',
        'sub_filter',
    );
    const REGION_LIST_CACHE = 'searchRegionRendered';
    const CITY_LIST_CACHE = 'searchCitiesRendered_';
    const PART_LIST_CACHE = 'searchPartsRendered';
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
//            $filters = Model_BoardFilter::loadFiltersByCategory($category_id, TRUE);
//            Model_BoardFilter::loadSearchFilterValues($filters, Arr::get($_GET, 'filters', array()));
//            $filters_view = View::factory('widgets/_board_filters_search_list', array(
//                'filters' => $filters,
//            ))->render();
            if($category_id)
                $category_name = Model_BoardCategory::getField('name', $category_id);
        }
//        if(!$price_filter = Arr::get($_GET, 'price'))
//            $price_filter = array('from'=>NULL, 'to'=>NULL);

        /* REGION NAME */
        $region_name = '';
        if($city_alias){
            $region_id = Model_BoardCity::getCityIdByAlias($city_alias);
            if($region_id)
                $region_name = Model_BoardCity::getField('name', $region_id);
        }

        $this->template->set(array(
            'form_action' => $form_action,

            'region_name' =>  $region_name,
            'region_ailas' => $city_alias,
//            'city_list' => $this->_regionListRender(),

            'category_name' => $category_name,
            'category_alias' => $cat_alias,
//            'category_list' => $this->_categoryListRender(),
//            'filters' => isset($filters_view) ? $filters_view : '',

//            'price_filter' => $price_filter,
            'is_job_category' => isset($category_id) && in_array($category_id, Model_BoardCategory::getJobIds()),
            'priced_category' => !(isset($category_id) && in_array($category_id, Model_BoardCategory::getNopriceIds())),
        ));
    }

    /**
     * Get regions list
     */
    public function action_parts(){
        if(!$this->request->is_ajax())
            return NULL;
        $this->json['content'] = $this->_partListRender();
        echo json_encode($this->json);
    }

    /**
     * Get regions list
     */
    public function action_categories(){
        $part = (int) Request::current()->post('part_id');
        if(!$this->request->is_ajax())
            return NULL;
        $this->json['content'] = $this->_categoryListRender($part);
        echo json_encode($this->json);
    }

    /**
     * Get regions list
     */
    public function action_regions(){
        if(!$this->request->is_ajax())
            return NULL;
        $this->json['content'] = $this->_regionListRender();
        echo json_encode($this->json);
    }

    /**
     * Get cities list
     */
    public function action_cities(){
        $region = (int) Request::current()->post('region_id');
//        if(!$this->request->is_ajax() || !$region)
//            return NULL;
        $this->json['content'] = $this->_citiesListRender($region);
        echo json_encode($this->json);
    }

    /**
     * Get filters and render filters list
     * @return null
     * @throws Cache_Exception
     * @throws View_Exception
     */
    public function action_filters(){
        if(!$this->request->is_ajax())
            return NULL;
        $category = (int) Model_BoardCategory::getCategoryIdByAlias(Request::current()->post('category'));
        $query = Request::current()->post('query');
        $mainfilter = (int) Request::current()->post('mainfilter');
        $category = ORM::factory('BoardCategory', $category);
        $filters = array();
        parse_str($query, $post);
        if($category->loaded()){
            if($mainfilter){
                $main_filter = Model_BoardFilter::loadMainFilter($category->id);
                $post['filters'][$main_filter['id']] = $mainfilter;
            }
            $filters = Model_BoardFilter::loadFiltersByCategory($category->id);
            Model_BoardFilter::loadSearchFilterValues($filters, Arr::get($post, 'filters', array()));
        }
        $template = View::factory('widgets/_board_filters_search_list')->set(array(
            'filters' => $filters,
            'priced_category' => !(isset($category->id) && in_array($category->id, Model_BoardCategory::getNopriceIds())),
            'price_filter' => Arr::get($post, 'price', array()),
        ));
        $this->json['content'] = $template->render();
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
            Cache::instance()->set(self::REGION_LIST_CACHE, $content, Date::YEAR);
        }
        return $content;
    }

    protected function _citiesListRender($region){
        if(NULL === ($content = Cache::instance()->get(self::CITY_LIST_CACHE.$region))){
            $region = DB::select('id', 'alias', 'name')->from(ORM::factory('BoardCity')->table_name())->where('id','=',$region)->and_where('lvl','=',1)->order_by('name')->as_assoc()->execute();
            $region = $region[0];
            $cities = DB::select('id', 'alias', 'parent_id', 'name')->from(ORM::factory('BoardCity')->table_name())->where('lvl','=',2)->where('parent_id','=',$region['id'])->order_by('name')->as_assoc()->execute();
            $template = View::factory('widgets/_board_cities_search_list')->set(array(
                'cities' => $cities,
                'region' => $region,
            ));
            $content = $template->render();
            Cache::instance()->set(self::CITY_LIST_CACHE . $region['id'], $content, Date::YEAR);
        }
        return $content;
    }

    /**
     * Categories lists renderer
     * @return mixed|string
     */
    protected function _partListRender(){
//        if(NULL === ($content = Cache::instance()->get(self::PART_LIST_CACHE))){
            $result = DB::select('id', 'alias', 'name')->from(ORM::factory('BoardCategory')->table_name())->where('lvl','=',1)->order_by('name')->as_assoc()->execute();
            $parts = array();

            foreach($result as $part){
                $part['link'] = URL::base() . Route::get('board_cat')->uri(array(
                    'cat_alias' => $part['alias'],
                ));
                $parts[] = $part;
            }
            $template = View::factory('widgets/_board_part_search_list')->set(array(
                'parts'  => $parts,
//                'all_uri'  => Route::get('board_cat')->uri(),
            ));
            $content = $template->render();
//            Cache::instance()->set(self::PART_LIST_CACHE, $content, Date::YEAR);
//        }
        return $content;
    }

    /**
     * Categories lists renderer
     * @return mixed|string
     */
    protected function _categoryListRender($part_id){
        if(NULL === ($content = Cache::instance()->get(self::CATEGORY_LIST_CACHE.$part_id))){
            $part = DB::select('id', 'alias', 'name')->from(ORM::factory('BoardCategory')->table_name())->where('id','=',$part_id)->and_where('lvl','=',1)->order_by('name')->as_assoc()->execute();
            $part = $part[0];
            $part['link'] = URL::base() . Route::get('board_cat')->uri(array(
                    'cat_alias' => $part['alias'],
                ));
            $result = DB::select('id', 'alias', 'parent_id', 'name')->from(ORM::factory('BoardCategory')->table_name())->where('lvl','=',2)->and_where('parent_id','=',$part_id)->order_by('name')->as_assoc()->execute();
            $subcats = array();
            foreach($result as $res)
                $subcats[] = $res;
            $template = View::factory('widgets/_board_category_search_list')->set(array(
                'part'  => $part,
                'subcats'  => $subcats,
//                'all_uri'  => Route::get('board_cat')->uri(),
            ));
            $content = $template->render();
            Cache::instance()->set(self::CATEGORY_LIST_CACHE.$part_id, $content, Date::YEAR);
        }
        return $content;
    }

    /**
     * Categories lists renderer
     * @return mixed|string
     */
    protected function _full_categoryListRender(){
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