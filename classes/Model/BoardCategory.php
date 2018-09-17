<?php defined('SYSPATH') OR die('No direct script access.');

class Model_BoardCategory extends ORM_MPTT{

    CONST BOARD_CATEGORIES_CACHE = 'boardCategoriesList';
    CONST BOARD_TREE_CACHE = 'boardCategoriesTree';
    CONST BOARD_ALIASES_CACHE = 'boardCategoriesAliases';
    CONST BOARD_NAMES_CACHE = 'boardCategoriesNames';

    CONST TWO_LEVEL_CACHE = 'boardTwoLevelCategories';
    CONST FULL_DEPTH_CACHE = 'boardFullDepthCategories';
    CONST PARENTS_CACHE = 'boardCategoryParents_';
    CONST CHILDREN_CACHE = 'boardCategoryChilds_';

    CONST CATEGORIES_CACHE_TIME = 2592000;

    protected $_reload_on_wakeup   = FALSE;

    protected $_table_name = 'ad_categories';
    protected $_uriToMe;

    public static $categories;
    public static $aliases;
    public static $fields = array();

    protected static $_exclude_from_chaching = array(
        'seo',
    );

    public function labels(){
        return array(
            'id' => 'Id',
            'name' => 'Name',
            'alias' => 'Alias',
            'job' => 'Job category',
            'noprice' => 'No prices category',
            'parent_id' => 'Parent category',
            'subcats' => 'Sub Categories',
            'title' => 'Meta Title',
            'description' => 'Meta Description',
            'seo' => 'Seo Text',
        );
    }


    public function filters(){
        return array(
            'alias' => array(
                array(array($this,'generateAlias'))
            ),
        );
    }

    /**
     * Generate transliterated alias
     */
    public function generateAlias($alias){
        $alias = trim($alias);
        if(empty($alias))
            $alias = Text::transliterate($this->name, true);
        return $alias;
    }

    /**
     * Create cached categories list
     * @return mixed
     */
    public static function getCategoriesList(){
        if(NULL === $array = Cache::instance()->get(self::BOARD_CATEGORIES_CACHE)){
            $list = ORM::factory('BoardCategory')
                ->where('lvl','>','0')
                ->find_all()
                ->as_array('id');
            if(is_array($list))
                foreach($list as $id=>$category){
                    $array[$id] = static::_removeExcluded($category, static::$_exclude_from_chaching);
                }
            Cache::instance()->set(self::BOARD_CATEGORIES_CACHE, $array, self::CATEGORIES_CACHE_TIME);
        }
        return $array;
    }

    /**
     *
     */
    public static function  getCategoriesTree(){
        if(NULL === $array = Cache::instance()->get(self::BOARD_TREE_CACHE)){
            $list = ORM::factory('BoardCategory')
                ->order_by('name', 'ASC')
                ->find_all()
                ->as_array('id');
            if(is_array($list))
                foreach($list as $id=>$category){
                    $array[$id] = static::_removeExcluded($category, static::$_exclude_from_chaching);
                    $array[$category->parent_id][$id] = $category;
                }
            Cache::instance()->set(self::BOARD_TREE_CACHE, $array, self::CATEGORIES_CACHE_TIME);
        }
        return $array;
    }

    /**
     * Create cached category aliases list
     * @param $alias
     * @return array|mixed
     */
    public static function getAliases($alias = NULL){
        if(!is_null(self::$aliases)){
            if(!is_null($alias))
                return isset(self::$aliases[$alias]) ? self::$aliases[$alias] : false;
            return self::$aliases;
        }
        if(NULL === $array = Cache::instance()->get(self::BOARD_ALIASES_CACHE)){
            $array = ORM::factory('BoardCategory')->find_all()->as_array('alias', 'id');
            Cache::instance()->set(self::BOARD_ALIASES_CACHE, $array, self::CATEGORIES_CACHE_TIME);
        }
        self::$aliases = $array;
        if(!is_null(self::$aliases)){
            if(!is_null($alias))
                return isset(self::$aliases[$alias]) ? self::$aliases[$alias] : false;
            return self::$aliases;
        }
    }


    /**
     * Creates cached array width ID as key and Field value as value
     * @param $field
     * @param $id
     * @return array|mixed
     */
    public static function getField($field, $id = null){
        if(isset(self::$fields[$field])){
            if(!is_null($id))
                return isset(self::$fields[$field][$id]) ? self::$fields[$field][$id] : false;
            return  self::$fields[$field];
        }
        if(NULL === $array = Cache::instance()->get('BoardCategoryFieldArray'.ucfirst($field))){
            $array = ORM::factory('BoardCategory')->find_all()->as_array('id', $field);
            Cache::instance()->set('BoardCategoryFieldArray'.ucfirst($field), $array, self::CATEGORIES_CACHE_TIME);
        }
        self::$fields[$field] = $array;
        if(!is_null($id))
            return isset(self::$fields[$field][$id]) ? self::$fields[$field][$id] : false;
        return  self::$fields[$field];
    }

    /**
     * Geting category ID by Alias
     * @param mixed $alias
     * @return bool
     */
    public static function getCategoryIdByAlias($alias){
        if((int) ($id = self::getAliases($alias))){
            return $id;
        }
        return false;
    }

    /**
     * Get Category Name with dotted padding by level deepness
     * @param int $root_level
     * @return string
     */
    public function getLeveledName($root_level = 1){
        return str_repeat('.&nbsp;.&nbsp;',$this->lvl - $root_level) . $this->name;
    }

    /**
     * Загрузка категорий первого и второго уровня уровня
     * во вложеный массив (для вывода в SELECT c группами)
     * @return array|mixed
     */
    public static function getTwoLevelArray(){
        Cache::instance()->delete(self::TWO_LEVEL_CACHE);
        if(NULL === $array = Cache::instance()->get(self::TWO_LEVEL_CACHE)){
            $array = array();
            $list = ORM::factory('BoardCategory')
                ->where('lvl','<=','2')
                ->where('lvl','>','0')
                ->find_all()
                ->as_array('id');
            if(is_array($list))
                foreach($list as $id=>$category){
                    if($category->lvl > 1)
                        $array[$list[$category->parent_id]->name][$id] = $category->name;
                }
            Cache::instance()->set(self::TWO_LEVEL_CACHE, $array, self::CATEGORIES_CACHE_TIME);
        }
        return $array;
    }

    /**
     * Загрузка категорий первого уровня (для вывода в SELECT)
     * @return array|mixed
     */
    public function getFullDepthArray(){
        if(NULL === $array = Cache::instance()->get(self::FULL_DEPTH_CACHE)){
            $array = array();
            $list = $this->fulltree()->as_array('id');
            if(is_array($list))
                foreach($list as $id=>$category)
                    $array[$id] = $category->getLeveledName();
            Cache::instance()->set(self::FULL_DEPTH_CACHE, $array, self::CATEGORIES_CACHE_TIME);
        }
        return $array;
    }

    /**
     * Получить список ключей родителей категории
     * @param $id
     * @return array
     */
    public function getParentsId($id){
        if(NULL === $array = Cache::instance()->get(self::PARENTS_CACHE . $id)){
            $list = ORM::factory('BoardCategory', $id)->parents()->as_array('id', 'name');
            if(is_array($list))
                $array = array_keys($list);
            Cache::instance()->set(self::PARENTS_CACHE . $id, $array, self::CATEGORIES_CACHE_TIME);
        }
        return $array;
    }

    /**
     * Получить список ключей детей категории
     * @param $id           - категория родитель
     * @return array
     */
    public function getChildrenId($id = NULL){
        if(NULL === $array = Cache::instance()->get(self::CHILDREN_CACHE . $id)){
            $category = $this;
            if($id)
                $category = ORM::factory('BoardCategory', $id);
            $array = $category->children(TRUE)->as_array('id', 'id');
            Cache::instance()->set(self::CHILDREN_CACHE . $id, $array, self::CATEGORIES_CACHE_TIME);
        }
        return $array;
    }

    /**
     * Get category uri
     * @param null $city_alias
     * @return string
     * @throws Kohana_Exception
     */
    public function getUri($city_alias = NULL, $reset = false){
        if(is_null($this->_uriToMe) || $reset){
            $this->_uriToMe = Route::get('board_cat')->uri(array(
                'cat_alias' => $this->alias,
                'city_alias' => $city_alias,
            ));
        }
        return $this->_uriToMe;
    }

    /**
     * Generate content for META Title tag
     * @return string
     */
    public function getTitle(){
        if(!empty($this->title))
            $title = $this->title;
        else
            $title = $this->name;
        return htmlspecialchars($title);
    }

    /**
     * Generate content for META Description tag
     * @return string
     */
    public function getDescription(){
        if(!empty($this->description))
            $description = $this->description;
        else
            $description = $this->name;
        return htmlspecialchars($description);
    }

    /**
     * Generate runtime URI
     * @param $alias
     * @return string
     * @throws Kohana_Exception
     */
    public static function generateUri($alias){
        $uri = Route::get('board_cat')->uri(array(
            'cat_alias' => $alias,
            'city_alias' => Request::initial()->param('city_alias'),
        ));
        return $uri;
    }

    /**
     * Get IDs of all JOB categories
     * @return array
     * @throws Kohana_Exception
     */
    public static function getJobIds(){
        return ORM::factory('BoardCategory')->select('id')->where('job','=',1)->cached(Model_BoardCategory::CATEGORIES_CACHE_TIME)->find_all()->as_array('id','id');
    }

    /**
     * Get IDs of all NOPRICE categories
     * @return array
     * @throws Kohana_Exception
     */
    public static function getNopriceIds(){
        return ORM::factory('BoardCategory')->select('id')->where('noprice','=',1)->cached(Model_BoardCategory::CATEGORIES_CACHE_TIME)->find_all()->as_array('id','id');
    }


    /**
     * Request module parts links array for sitemap generation
     * @return array
     */
    public function sitemapCategories($config){
	    $step = 10000;
	    $path = 'media/upload/sitemap/';

	    $priority = isset($config['priority']) ? $config['priority'] : '0.5';
	    $frequency = isset($config['frequency']) ? $config['frequency'] : 'daily';

	    $cities = ORM::factory('BoardCity')->find_all()->as_array('id', 'alias');
	    $categories= ORM::factory('BoardCategory')->find_all();

	    $sitemaps = array();

	    $sitemap = new Sitemap();
	    $sitemap->gzip = TRUE;
	    $url = new Sitemap_URL;

	    $i = 1;
	    $links_cnt = 0;
	    foreach ($categories as $category){
	    	foreach ($cities as $city_id=>$city_alias){
			    $url->set_loc(URL::base(KoMS::protocol()).$category->getUri($city_alias, true))
			        ->set_last_mod(time())
			        ->set_change_frequency($frequency)
			        ->set_priority($priority);
			    $sitemap->add($url);
			    $links_cnt++;

			    // Save next sitemap
			    if($links_cnt >= $step){
				    $file = DOCROOT . $path . "board_categories_".$i.".xml.gz";
				    $sitemap_link = URL::base(KoMS::protocol()). $path ."board_categories_".$i.".xml.gz";
				    $sitemaps[] = $sitemap_link;
				    $response = $sitemap->render();
				    file_put_contents($file, $response);
				    $i++;

				    // New Sitemap File
				    unset($sitemap);
				    $sitemap = new Sitemap();
				    $sitemap->gzip = TRUE;
				    $url = new Sitemap_URL;
				    $links_cnt = 0;
			    }
		    }
	    }
	    $file = DOCROOT . $path . "board_categories_".$i.".xml.gz";
	    $sitemap_link = URL::base(KoMS::protocol()). $path ."board_categories_".$i.".xml.gz";
	    $response = $sitemap->render();
	    file_put_contents($file, $response);
	    $sitemaps[] = $sitemap_link;
	    return $sitemaps;
    }

	/**
	 * Ганенрирует список категорий для карты сайта
	 * @param array $config
	 * @return string
	 */
    public function simple_sitemapCategories($config){
        $links = array();
	    $path = 'media/upload/sitemap/';

        $categories = $this->getCategoriesList();

	    $sitemap = new Sitemap();
	    $sitemap->gzip = TRUE;
	    $sitemap_link = URL::base(KoMS::protocol()). $path ."categories.xml.gz";

	    $priority = isset($config['priority']) ? $config['priority'] : '0.5';
	    $frequency = isset($config['frequency']) ? $config['frequency'] : 'weekly';

	    $url = new Sitemap_URL;
        foreach($categories as $category){
	        $url->set_loc(URL::base(KoMS::protocol()).$category->getUri())
	            ->set_last_mod( time() )
	            ->set_change_frequency($frequency)
	            ->set_priority($priority);
	        $sitemap->add($url);
        }

	    $response = $sitemap->render();
	    $file = DOCROOT . $path . "categories.xml.gz";
	    file_put_contents($file, $response);

	    return array($sitemap_link);
    }

    /**
     * Counts all ads in categories
     * Return counts array
     * array(
     *      city_id=>count,
     *      ...
     *      city_id=>count,
     * )
     * @param int|null $category_id
     * @param int|null $city_id
     * @return array
     * @throws Kohana_Exception
     */
    public static function categoryCounter($category_id = NULL, $city_id=NULL){
        if($category_id > 0)
            $sql = DB::select(array('category_id','cat_id'), array(DB::expr('count(*)'), 'cnt'))->from( ORM::factory('BoardAd')->table_name() )->where('publish','=','1')->and_where('pcategory_id', '=', $category_id);
        else
            $sql = DB::select(array('pcategory_id', 'cat_id'), array(DB::expr('count(*)'), 'cnt'))->from( ORM::factory('BoardAd')->table_name() )->where('publish','=','1');
        if($city_id)
            $sql->and_where( (Model_BoardCity::getField('parent_id', $city_id) ? '' : 'p') .'city_id', '=', $city_id);
        $sql->group_by('cat_id')->order_by('cnt', 'DESC')->cached(Date::HOUR);
        return $sql->execute()->as_array('cat_id', 'cnt');
    }

    /**
     * Добавить категорию
     * @param $categories
     * @param $row
     * @param $parent_id
     */
    public static function import_category(&$categories, $row, $parent_id = 0){
        /**
         * @var ORM_MPTT $new
         */
        $new = ORM::factory('BoardCategoryJB')->values(array(
            'id' => $row['id'],
            'name' => $row['name_cat'],
            'alias' => $row['en_name_cat'],
        ));
        if($row['root_category']){
            /**
             * @var ORM_MPTT $parent
             */
            $parent = ORM::factory('BoardCategoryJB', $parent_id);
            $new->insert_as_last_child($parent);
        }
        else{
//            $parent = ORM::factory('BoardCategory', 1);
//            if(!$parent->loaded())
//                throw new Kohana_Exception('Parent model not found');
//            $new->insert_as_last_child($parent);
            $new->make_root();
        }
        $new->id = $row['id'];
        $new->save();
        if(isset($categories[$row['id']]) && count($categories[$row['id']])){
            foreach($categories[$row['id']] as $_row)
                self::import_category($categories, $_row, $new->id);
        }
    }

    /**
     * Smart article field getter
     * @param string $name
     * @return mixed|string
     */
    public function __get($name){
//        if($name == 'categories_list'){
//            if(is_null(self::$categories))
//                return self::getCategoriesList();
//            return self::$categories;
//        }
        if($name == 'aliases_list'){
            if(is_null(self::$aliases))
                return self::getAliases();
            return self::$aliases;
        }
        return parent::__get($name);
    }

    /**
     * Remove excluded fields from object
     * @param ORM|ORM_MPTT $object - object to operate
     * @param array $remove - array of fields to remove
     * @return ORM|ORM_MPTT
     */
    protected static function _removeExcluded(&$object, Array $remove){
        foreach ($remove as $field)
            if(isset($object->$field))
                unset($object->$field);
        return $object;
    }
}