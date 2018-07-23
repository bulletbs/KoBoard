<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Board search pages caching class
 * Class BoardSearch
 */
class BoardSearch {

    protected static $_instance;

    public $title_type;
    public $title_params;
    public $title_templates;

    public $title;
	public $description;
    public $pagetitle;
    public $subtitle;
    public $nothing_found_text;

    public $finder;
    public $count;
	public $template = array();
	public $pagination;
	public $breadcrumbs;

    public $ads;
    public $photos;
    public $terms;

    public $city;
    public $city_alias;

    public $category;
    public $category_alias;
    public $childs_categories;

	public $main_filter;
	public $filter_alias;
	public $query;
	public $term;

    public $user;
    public $username;
    public $searchByUser;


    /**
     * Creates config instance
     * @return BoardSearch
     */
    public static function instance(){
        if(is_null(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
     * Constructs a new search instance
     */
    public function __construct()
    {
	    $this->city_alias = Request::current()->param('city_alias');
	    if(is_null($this->city_alias))
	        $this->city_alias = BoardConfig::instance()->country_alias;
	    $this->category_alias = Request::current()->param('cat_alias');

	    $this->breadcrumbs = array(array(KoMS::config()->breadcrumb_root, '/'));
    }


	/**
	 * Инициализация поиска
	 */
    public function init(){
	    $this->finder = Model_BoardAd::boardOrmFinder();
	    $this->_addCity()
	         ->_addCategory()
	         ->_categoryCounter()
	         ->_cityCounter()
	         ->_addText()
	         ->_addUser()
	         ->_addPrice()
	         ->_addMainFilter()
	         ->_addFilters()
	         ->_addType()
	    ;
    }

	/**
	 * Внешняя обертка поиска
	 */
    public function search(){
	    $this->_searchAds();

	    if($this->searchByUser){
		    $this->template += array(
			    'search_by_user' => true,
		    );
	    }
	    else{
		    $this->template += array(
                'search_by_user' => false,
			    'city' => $this->city,
			    'category' => $this->category,
			    'childs_categories' => $this->childs_categories,
		    );
	    }
	    $this->template += array(
		    'ads' => $this->ads,
		    'photos' => $this->photos,
		    'terms' => $this->terms,
		    'pagination' => $this->pagination,
	    );
    }

	/**
	 * Отобажение кешированных страниц
	 */
    public function cached(){

    }


	/**
	 * Выборка объявлений и вывода страниц
	 */
    protected function _searchAds(){
    	/* Paginate */
	    $sql = str_replace('`ads`.*', 'COUNT(*) AS cnt', (string) $this->finder);
	    $this->count = DB::query(Database::SELECT, $sql)->cached(Model_BoardAd::CACHE_TIME)->as_assoc()->execute();
	    $this->count = $this->count['0']['cnt'];
	    $route_params = array(
		    'controller' => Request::current()->controller(),
		    'city_alias' => $this->city_alias,
		    'cat_alias' => $this->category_alias,
	    );
	    if($this->user instanceof Model_User && $this->user->loaded())
		    $route_params['user'] = $this->user->id;
	    if(!is_null($this->term))
		    $route_params['term'] = $this->term;
	    $this->pagination = Pagination::factory(array(
		    'total_items' => $this->count,
		    'group' => 'board',
	    ))->route_params($route_params);

	    /* Requesting ads */
	    $this->finder->offset($this->pagination->offset)->limit($this->pagination->items_per_page);
	    $this->ads = $this->finder->execute();
	    $ads_ids = array();
	    $ads_titles = array();
	    foreach($this->ads as $_ad){
            $ads_ids[] = $_ad->id;
            $ads_titles[] = $_ad->title;
        }
        $this->photos = Model_BoardAdphoto::adsPhotoList($ads_ids);
        $this->terms = BoardTerms::seokeywords(implode(' ', $ads_titles),5, 5);
    }


	/**
	 * Добавить в поиск алиас региона
	 * @return $this
	 * @throws HTTP_Exception
	 */
    protected function _addCity(){
	    if(!is_null($this->city_alias) && $this->city_alias!= BoardConfig::instance()->country_alias){
		    if(FALSE === ($this->city = Model_BoardCity::getAliases($this->city_alias)))
			    throw HTTP_Exception::factory('404', __('Page not found'));
		    $this->city = ORM::factory('BoardCity', $this->city)->fillNames();

		    /* Region breadcrumbs */
		    if(BoardConfig::instance()->breadcrumbs_search_region_all){
			    $parents = $this->city->parents()->as_array('id');
			    foreach($parents as $_parent)
				    $this->breadcrumbs[] = array($_parent->name, $_parent->getUri());
			    if(BoardConfig::instance()->breadcrumbs_region_title)
				    $this->breadcrumbs[] = array($this->city->name, $this->city->getUri());
		    }
		    else{
			    $this->breadcrumbs = array(
			    	array(BoardConfig::instance()->breadcrumbs_prefix.$this->city->name_of, $this->city->getUri())
			    );
		    }

		    if(!$this->city->parent_id){
			    $this->finder->and_where('pcity_id','=',$this->city->id);
		    }
		    else{
			    $this->finder->and_where('city_id','=',$this->city->id);
		    }
	    }
	    if(is_null($this->city))
		    $this->template += array(
			    'regions' => Model_BoardCity::getRegionsArray(),
		    );
	    return $this;
    }


	/**
	 * Добавить в поиск алиас категории
	 * @return $this
	 * @throws HTTP_Exception
	 */
    protected function _addCategory(){
	    if(!is_null($this->category_alias)){
		    if(FALSE === ($this->category = Model_BoardCategory::getAliases($this->category_alias)))
			    throw HTTP_Exception::factory('404', __('Page not found'));
		    if(!BoardConfig::instance()->breadcrumbs_region_title && $this->city instanceof ORM)
			    $this->breadcrumbs[] = array($this->city->name, $this->city->getUri());
		    $this->category = ORM::factory('BoardCategory', $this->category);
		    $parents = $this->category->parents()->as_array('id');
		    foreach($parents as $_parent)
			    $this->breadcrumbs[] = array($_parent->name, $_parent->getUri($this->city_alias));
		    if(BoardConfig::instance()->breadcrumbs_category_title)
			    $this->breadcrumbs[] = array($this->category->name, $this->category->getUri($this->city_alias));
		    $this->childs_categories = ORM::factory('BoardCategory')->where('parent_id','=',$this->category->id)->order_by('name', 'ASC')->find_all()->as_array('id');
		    if(!$this->category->parent_id){
			    $this->finder->and_where('pcategory_id','=',$this->category->id);
		    }
		    else{
			    $this->finder->and_where('category_id','=',$this->category->id);
		    }
	    }
	    else{
		    $this->childs_categories = ORM::factory('BoardCategory')->where('lvl','=','1')->cached(Model_BoardCategory::CATEGORIES_CACHE_TIME)->order_by('name')->find_all()->as_array('id');
	    }
	    return $this;
    }


	/**
	 * Подсчет объявлений в регионе
	 * @return $this
	 */
    protected function _cityCounter(){
	    if(!$this->city instanceof ORM || !$this->city->parent_id){
		    $city_id = $this->city instanceof ORM ? $this->city->id : NULL;
		    $category_id = $this->category instanceof ORM ? $this->category->id : NULL;
		    $ads_count = Model_BoardCity::regionCounter($city_id, $category_id, 0.5);
		    $this->template += array(
			    'city_counter'=> $ads_count['all'],
			    'big_city_counter'=> $ads_count['big'],
		    );
	    }
	    return $this;
    }


	/**
	 * Подсчет объявлений в категории
	 * @return $this
	 */
	protected function _categoryCounter(){
	    if(!$this->category instanceof ORM || !$this->category->parent_id){
		    $category_id = $this->category instanceof ORM ? $this->category->id : NULL;
		    $city_id = $this->city instanceof ORM ? $this->city->id : NULL;
		    $counter = Model_BoardCategory::categoryCounter($category_id, $city_id);
		    $counter = array_intersect_key($counter, $this->childs_categories);
		    $this->template += array(
			    'category_counter'=> $counter,
		    );
	    }
		return $this;
    }


	/**
	 * Добавить в поиск текстовую фразу
	 * @throws HTTP_Exception_200
	 */
    protected function _addText(){
	    $this->query = Arr::get($_GET, 'query');
	    $this->term = Request::current()->param('term');
	    if(empty($this->query) && !is_null($this->term))
	        $this->query = $this->term;
	    if(!empty($this->query)){
		    $this->query = Text::stripSQL(urldecode($this->query));
		    $_GET['query'] = $this->query;
		    if(!empty($this->query) && mb_strlen($this->query) >= 5){
			    $this->finder->and_where(DB::expr('MATCH(`title`'.(Arr::get($_GET, 'wdesc') > 0 ? ',description': '').')'), 'AGAINST', DB::expr("('".Arr::get($_GET, 'query')."' IN BOOLEAN MODE)"));
			    /* Save search statistics */
			    if(Request::current()->param('page') == NULL){
				    try{
					    $search = Model_BoardSearch::findTag($this->query, $this->category instanceof ORM ? $this->category->id : 0);
					    if(!$search->loaded()){
						    Model_BoardSearch::createTag($this->query, $this->category instanceof ORM ? $this->category->id : 0);
					    }
					    else{
						    $search->cnt++;
						    $search->update();
					    }
				    }
				    catch(ORM_Validation_Exception $e){
					    $e->getMessage();
				    }
			    }
		    }
		    // выдавать страницу с ошибкой, если ищут менее 5 символов и не выбран регион или категория
		    elseif($this->city_alias == BoardConfig::instance()->country_alias && !$this->category instanceof ORM && mb_strlen($this->query) < 5){
			    throw new HTTP_Exception_200(__('Minimal allowed length of search query is :char chars', array(':char'=>5)));
		    }
		    else{
			    unset($_GET['query']);
			    unset($this->query);
		    }
	    }
	    return $this;
    }


	/**
	 * Добавить в поиск пользователя
	 * @throws HTTP_Exception_404
	 */
    protected function _addUser(){
	    $this->user = Request::current()->param('user');
	    if(!is_null($this->user)){
		    $this->user = ORM::factory('User', (int) $this->user);
		    if($this->user->loaded()){
			    $this->finder->and_where('user_id', '=', $this->user->id);
			    $this->username = $this->user->profile->name;
			    $this->searchByUser = true;
		    }
		    else{
			    throw new HTTP_Exception_404();
		    }
	    }
	    return $this;
    }


	/**
	 * Добавить в поиск интервал цен
	 */
    protected function _addPrice(){
	    if(is_array($price = Arr::get($_GET, 'price')) && ((int) Arr::get($price, 'from')>0 || (int) Arr::get($price, 'to')>0) ){
		    if((int) Arr::get($price, 'from'))
			    $this->finder->and_where('price', '>=', $price['from']);
		    if((int) Arr::get($price, 'to'))
			    $this->finder->and_where('price', '<=', $price['to']);
	    }
	    return $this;
    }


	/**
	 * Добавить в поиск главный филтр категории
	 * @throws HTTP_Exception
	 */
    protected function _addMainFilter(){
	    if($this->category instanceof ORM && FALSE !== ($this->main_filter = Model_BoardFilter::loadMainFilter($this->category->id))){
		    $this->main_filter['base_uri'] = URL::site(Route::get('board_subcat')->uri(array(
			    'cat_alias' => Request::current()->param('cat_alias'),
			    'city_alias' => Request::current()->param('city_alias'),
			    'filter_alias' => '{{ALIAS}}',
		    )));
		    if(NULL !== ($this->filter_alias = Request::$current->param('filter_alias'))){
			    if(!isset($this->main_filter['aliases'][$this->filter_alias]))
				    throw HTTP_Exception::factory('404', __('Page not found'));
			    $_GET['filters'][$this->main_filter['id']] = $this->main_filter['aliases'][$this->filter_alias];
			    $main_filter['value'] = $this->main_filter['aliases'][$this->filter_alias];
		    }
		    // опустошить фильтр, когда значение уже выбрано
		    if($this->main_filter['aliases'][$this->filter_alias])
			    $this->main_filter['options'] = array();
		    $main_filter_count = Model_BoardFilter::filterCounter($this->main_filter['id'], $this->city);
		    $this->template += array(
			    'main_filter'=>Model_BoardFilter::clearMainFilterOptions($this->main_filter, $main_filter_count),
			    'main_filter_cnt'=>$main_filter_count,
		    );
	    }
	    return $this;
    }


	/**
	 * Добавить в поиск фильтры
	 * @return $this
	 */
    protected function _addFilters(){
	    if($this->category instanceof ORM && NULL !== ($filters_values = Arr::get($_GET, 'filters')) && Model_BoardFiltervalue::haveValues($filters_values)){
		    $filters = Model_BoardFilter::loadFiltersByCategory($this->category->id);
		    /* При выбраном главном фильтре устанавливаем title, h1 и добавляем в хлебные крошки */
		    if(isset($this->main_filter) && isset($filters_values[$this->main_filter['id']]) && !is_array($filters_values[$this->main_filter['id']])){
			    $this->main_filter['selected_name'] = $filters[$this->main_filter['id']]['options'][ $filters_values[$this->main_filter['id']] ];
			    $this->title = $this->main_filter['selected_name'] . (!empty($this->title) ? ' '.$this->title : '' );
			    $this->breadcrumbs[] = array($this->main_filter['selected_name'], false);
		    }
		    if(count($filters)){
			    foreach($filters_values as $_id=>$_val){
				    if(Model_BoardFiltervalue::haveValue($_val) && isset($filters[$_id])){
					    $this->finder->join(array('ad_filter_values','afv'.$_id), 'INNER');
					    $this->finder->on('afv'.$_id.'.filter_id','=',DB::expr($_id));
					    $this->finder->on('afv'.$_id.'.ad_id', '=', 'ads.id');
					    if($filters[$_id]['type'] == 'digit' && ((int) Arr::get($_val, 'from') || (int) Arr::get($_val, 'to') )){
						    $this->finder->where_open();
						    if((int)Arr::get($_val, 'from'))
							    $this->finder->and_where('afv'.$_id.'.value', '>=', $_val['from']);
						    if((int)Arr::get($_val, 'to'))
							    $this->finder->and_where('afv'.$_id.'.value', '<=', $_val['to']);
						    $this->finder->where_close();
					    }
					    elseif($filters[$_id]['type'] == 'optlist'){
						    $_bin = Model_BoardFiltervalue::optlist2mysqlBin( array_flip($_val) );
						    $this->finder->and_where(DB::expr('afv'.$_id.'.value & '. $_bin), '=', DB::expr($_bin));
					    }
					    elseif($filters[$_id]['type'] == 'select' && is_array($_val) && count($_val)){
						    $this->finder->and_where('afv'.$_id.'.value', 'IN', $_val);
					    }
					    elseif(!empty($_val) && !is_array($_val)){
						    $this->finder->and_where('afv'.$_id.'.value', '=', $_val);
					    }
				    }
			    }
			    $this->finder->group_by('ads.id');
		    }
	    }
	    return $this;
    }


	/**
	 * Добавить ТИП в поиск
	 * @return $this
	 */
    protected function _addType(){
	    if(!is_null(Arr::get($_GET, 'type'))){
		    $this->finder->and_where('type', '=', Arr::get($_GET, 'type'));
	    }
	    return $this;
    }


	/**
	 * Добавить в поиск условие "только с фото"
	 * @return $this
	 */
    protected function _addPhoto(){
	    if(Arr::get($_GET, 'wphoto') > 0){
		    $this->finder->and_where('photo_count', '>', 0);
	    }
		return $this;
    }


	/**
	 * Check if no parameters - than 404, empty query - 200 error
	 * @throws HTTP_Exception_200
	 * @throws HTTP_Exception_404
	 */
    protected function _checkParameters(){
	    if($this->city_alias == BoardConfig::instance()->country_alias && !$this->category instanceof ORM && isset($_GET['query']) && empty($_GET['query']) && !isset($_GET['userfrom'])){
		    throw new HTTP_Exception_200(__('Minimal allowed length of search query is :char chars', array(':char'=>5)));
	    }
	    elseif(!BoardConfig::instance()->allow_all_search && $this->city_alias == BoardConfig::instance()->country_alias && !$this->category instanceof ORM && !isset($_GET['query']) && !isset($_GET['userfrom'])){
		    throw new HTTP_Exception_404();
	    }
    }


	/**
	 * Сгенерировать данные для тегов link canonical pagination
	 * @return array
	 */
	public function canonicalPagination(){
		$tags_array = array();
		$_page = max(1, Request::current()->param('page'));
		if($_page > 1){
			$tags_array[] = array(
				'tag' => 'link',
				'rel' => 'prev',
				'href' => URL::base(KoMS::protocol()).substr($this->pagination->url($_page-1), 1),
			);
		}
		if($_page < $this->pagination->total_pages){
			$tags_array[] = array(
				'tag' => 'link',
				'rel' => 'next',
				'href' => URL::base(KoMS::protocol()).substr($this->pagination->url($_page+1), 1),
			);
		}
		$tags_array[] =array(
			'tag' => 'link',
			'rel' => 'canonical',
			'href' => URL::base(KoMS::protocol()).substr($this->pagination->url(1), 1),
		);
		return $tags_array;
	}

	protected function _metaInit(){
		// Calc parameters
		$this->title_type = 'region_title';
		$this->title_params = array(
			'page' => $this->pagination->current_page,
			'project' => KoMSConfig::instance()->project['name'],
		);
		if($this->city instanceof ORM){
			$this->title_params['region'] = $this->city->name;
			$this->title_params['region_in'] = $this->city->name_in;
			$this->title_params['region_of'] = $this->city->name_of;
		}
		else{
			$this->title_params['region'] = BoardConfig::instance()->country_name;
			$this->title_params['region_in'] = BoardConfig::instance()->all_country;
			$this->title_params['region_of'] = BoardConfig::instance()->all_country;
		}
		if($this->category instanceof ORM) {
			$this->title_type = 'category_title';
			$this->title_params['category'] = isset($this->main_filter) && Arr::get($this->main_filter, 'selected_name', FALSE) ? $this->main_filter['selected_name'] : $this->category->name;
		}
		if(!is_null($this->city) && !is_null($this->category))
			$this->title_type = 'region_category_title';
		if(isset($this->term)){
            $this->title_type = 'tags_title';
            $this->title_params['tag'] = $this->term;
        }
		elseif(isset($this->query)){
			$this->title_type = 'query_title';
			$this->title_params['query'] = $this->query;
		}
		if($this->user instanceof Model_User && $this->user->loaded()){
			$this->title_type = 'user_search_title';
			$this->title_params['username'] = $this->user->profile->name;
		}

		// get templates
		$this->title_templates = BoardConfig::instance()->getValuesArray(array(
			'title' => $this->title_type,
			'description' => str_replace('title', 'description', $this->title_type),
			'h1' => str_replace('title', 'h1', $this->title_type),
			'h2' => str_replace('title', 'h2', $this->title_type),
			'empty' => str_replace('title', 'empty', $this->title_type),
		));
	}


	/**
	 * Генерация заголовков и описаний страницы
	 * @return array
	 */
	public function generateTitles(){
		// generate meta tags
		$meta_generator = MetaGenerator::instance()->setValues($this->title_params);
		if($this->title_type == 'category_title' || $this->title_type == 'region_category_title'){
			$this->title = !empty($this->category->title) ? $meta_generator->setTemplate($this->category->title)->generate() : '';
			$this->description = !empty($this->category->description) ? $meta_generator->setTemplate($this->category->description)->generate() : '';
		}
		// Generate meta title
		if(empty($this->title))
			$this->title = $meta_generator->setTemplate($this->title_templates['title'])->generate();
		// Generate meta description
		if(empty($this->description))
			$this->description = $meta_generator->setTemplate($this->title_templates['description'])->generate();

		// generate page titles
		$this->pagetitle = $meta_generator->setTemplate($this->title_templates['h1'])->generate();
		$this->subtitle = $meta_generator->setTemplate($this->title_templates['h2'])->generate();
		$this->nothing_found_text = $meta_generator->setTemplate($this->title_templates['empty'])->generate();

		return array(
			'title' => $this->title,
			'description' => $this->description,

			'pagetitle' => $this->pagetitle,
			'subtitle' => $this->subtitle,
			'nothing_found_text' => $this->nothing_found_text,
		);
	}


	/**
	 * Генерация списка МЕТА Тегов
	 * @return array
	 */
	public function generateMetaAdd() {
		$result = array(
			array( 'property' => 'og:title', 'content' => $this->title ),
			array( 'property' => 'og:type', 'content' => 'website' ),
			array( 'property' => 'og:url', 'content' => URL::base( Request::initial() ) ),
			array( 'property' => 'og:site_name', 'content' => KoMS::config()->project['name'] ),
			array( 'property' => 'og:description', 'content' => $this->description ),
			array(
				'property' => 'og:image',
				'content'  => URL::base( Request::initial() ) . "media/css/images/logo.png"
			),
		);
		return $result;
	}

	/**
	 * Список условий для замены META Тегов
	 * @return array
	 */
	public function generateMetaReplace(){
		$result = array();
		// robots tag related to results
		if($this->count == 0){
			$result = array(
				'name' => array(
					'name'=>'robots',
					'content'=>'noindex,nofollow',
				)
			);
		}
		return $result;
	}


	/**
	 * Генерация полных данных страницы (заголовки, мета-теги и прочее) для кеширования
	 * @return array
	 */
	public function generateFullPageData(){
		if(is_null($this->title_type))
			$this->_metaInit();
		$data = array();
		$data['meta_add'] = $this->generateMetaAdd();
		$data['meta_replace'] = $this->generateMetaReplace();
		$data['meta_canonical'] = $this->canonicalPagination();
		$data['titles'] = $this->generateTitles();
		$data['breadcrumbs'] = $this->breadcrumbs;
		return $data;
	}


	/**
	 * Save search page with parameters
	 * to cache file
	 * @param array $data - Page Data (content, aliases, counts, etc...)
	 */
	public function write_search_cache($data){
		if(!count($_GET) && in_array( Route::name(Request::current()->route()) , array('board_city','board_cat')))
			BoardCache::instance()->writeData(Request::current()->param(), $data);
	}


	/**
	 * Read cached search page
	 * @return string|false
	 */
	public function read_search_cache(){
		$content = false;
		if(!count($_GET) && in_array( Route::name(Request::current()->route()) , array('board_city','board_cat'))){
			$content = BoardCache::instance()->readData( Request::current()->param(), Date::DAY );
		}
		return $content;
	}


	/**
	 * Закомментированые методы
	 * @deprecated
	 */
	private function deprecatedFunctionality(){
//        $this->add_meta_content(array(
//            'name'=>'revisit-after',
//            'content'=>'1 days',
//        ));
//        $this->add_meta_content(array(
//            'tag' => 'link',
//            'rel' => 'canonical',
//            'href' => $pagination->url($pagination->current_page),
//        ));
	}


    /**
     * Config getter
     * @param $id
     * @return null
     */
    public function __get($id){
        if(isset($this->_config[ $id ]))
            return $this->_config[ $id ];
        return NULL;
    }


	/**
	 * Closed method
	 */
	private function __clone(){}
}