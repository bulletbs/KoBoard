<?php defined('SYSPATH') OR die('No direct script access.');

class Model_BoardCity extends ORM_MPTT{

    CONST BOARD_CITIES_CACHE = 'boardCitiesList';
    CONST BOARD_TREE_CACHE = 'boardCitiesList';
    CONST BOARD_ALIASES_CACHE = 'boardCitiesAliases';
    CONST BOARD_NAMES_CACHE = 'boardCitiesNames';

    CONST TWO_LEVEL_CACHE = 'boardTwoLevelCities';
    CONST FULL_DEPTH_CACHE = 'boardFullDepthCities';
    CONST PARENTS_CACHE = 'boardCityParents_';
    CONST CHILDREN_CACHE = 'boardCityChilds_';
    CONST REGIONS_CACHE = 'boardRegionsList';

    CONST CITIES_CACHE_TIME = 86400;

    protected $_reload_on_wakeup   = FALSE;

    protected $_table_name = 'ad_cities';
    protected $_uriToMe;

    public static $cities;
    public static $aliases;
    public static $fields = array();

    public function labels(){
        return array(
            'id' => __('Id'),
            'name' => __('Name'),
            'name_in' => __('Name IN'),
            'name_of' => __('Name OF'),
            'alias' => __('Alias'),
            'parent_id' => __('Parent region'),
            'subcats' => __('Cities'),
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
     * Create cached cities list
     * @param null $id
     * @return mixed
     */
    public static function getCitiesList($id = NULL){
        Cache::instance()->delete(self::BOARD_CITIES_CACHE);
        if(NULL === $array = Cache::instance()->get(self::BOARD_CITIES_CACHE)){
            $list = ORM::factory('BoardCity')
                ->find_all();
            if(count($list))
                foreach($list as $city){
                    $array[$city->id] = $city;
                }
            Cache::instance()->set(self::BOARD_CITIES_CACHE, $array, self::CITIES_CACHE_TIME);
        }

        return $id && isset($array[$id]) ? $array[$id] : $array;
    }

    /**
     * Create cached city aliases list
     * @param $alias
     * @return array|mixed
     */
    public static function getAliases($alias = NULL){
        $benchmark = Profiler::start('Cities', __FUNCTION__);
        if(!is_null(self::$aliases)){
            if(!is_null($alias))
                return isset(self::$aliases[$alias]) ? self::$aliases[$alias] : false;
            return  self::$aliases;
        }
        if(NULL === $array = Cache::instance()->get(self::BOARD_ALIASES_CACHE)){
            $array = ORM::factory('BoardCity')->find_all()->as_array('alias', 'id');
            Cache::instance()->set(self::BOARD_ALIASES_CACHE, $array, self::CITIES_CACHE_TIME);
        }
        self::$aliases = $array;
        Profiler::stop($benchmark);
        if(!is_null($alias))
            return isset(self::$aliases[$alias]) ? self::$aliases[$alias] : false;
        return self::$aliases;
    }

    /**
     * Creates cached array width ID as key and Field value as value
     * @param $field
     * @param $id
     * @return array|mixed
     */
    public static function getField($field, $id = null){
//        $benchmark = Profiler::start('Cities', __FUNCTION__);
        if(isset(self::$fields[$field])){
//            if($id == 0){
//                echo Debug::vars($field);
//                die($id);
//            }
            return !is_null($id) ? self::$fields[$field][$id] : self::$fields[$field];
        }
        if(NULL === $array = Cache::instance()->get('BoardCityFieldArray'.ucfirst($field))){
            $array = ORM::factory('BoardCity')->find_all()->as_array('id', $field);
            Cache::instance()->set('BoardCityFieldArray'.ucfirst($field), $array, self::CITIES_CACHE_TIME);
        }
        self::$fields[$field] = $array;
//        Profiler::stop($benchmark);
        return !is_null($id) ? self::$fields[$field][$id] : self::$fields[$field];;
    }

    /**
     * Geting city ID by Alias
     * @param mixed $alias
     * @return bool
     */
    public static function getCityIdByAlias($alias){
        if((int) ($id = self::getAliases($alias))){
            return $id;
        }
        return false;
    }

    /**
     * Get City Name with dotted padding by level deepness
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
    public function getTwoLevelArray(){
        Cache::instance()->delete(self::TWO_LEVEL_CACHE);
        if(NULL === $array = Cache::instance()->get(self::TWO_LEVEL_CACHE)){
            $array = array();
            $list = $this
                ->where('lvl','<=','2')
                ->find_all()
                ->as_array('id');
            if(is_array($list))
                foreach($list as $id=>$city){
                    if($city->lvl > 1)
                        $array[$list[$city->parent_id]->name][$id] = $city->name;
                }
            Cache::instance()->set(self::TWO_LEVEL_CACHE, $array, self::CITIES_CACHE_TIME);
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
                foreach($list as $id=>$city)
                    $array[$id] = $city->getLeveledName();
            Cache::instance()->set(self::FULL_DEPTH_CACHE, $array, self::CITIES_CACHE_TIME);
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
            $list = ORM::factory('BoardCity', $id)->parents()->as_array('id', 'name');
            if(is_array($list))
                $array = array_keys($list);
            Cache::instance()->set(self::PARENTS_CACHE . $id, $array, self::CITIES_CACHE_TIME);
        }
        return $array;
    }

    /**
     * Получить список ключей родителей категории
     * @return array
     */
    public static function getRegionsArray(){
        if(NULL === $list = Cache::instance()->get(self::REGIONS_CACHE)){
            $list = (array) ORM::factory('BoardCity')->where('parent_id','=',0)->order_by('name', 'ASC')->cached(Date::MONTH)->find_all()->as_array('id','name');
            Cache::instance()->set(self::REGIONS_CACHE , $list, Date::MONTH);
        }
        return $list;
    }

    /**
     * Получить список ключей детей региона
     * @param $id - категория родитель
     * @return array
     */
    public function getChildrenId($id = NULL){
        $cache_id = is_null($id) ? $this->id : $id;
        if(NULL === $array = Cache::instance()->get(self::CHILDREN_CACHE . $cache_id)){
            $city = $this;
            if($id)
                $city = ORM::factory('BoardCity', $id);
            $array = $city->children()->as_array('id', 'id');
            Cache::instance()->set(self::CHILDREN_CACHE . $cache_id, $array, self::CITIES_CACHE_TIME);
        }
        return $array;
    }

    public function getUri(){
        if(is_null($this->_uriToMe)){
            $this->_uriToMe = Route::get('board_city')->uri(array(
                'city_alias' => $this->alias,
            ));
        }
        return $this->_uriToMe;
    }

    public static function generateUri($alias, $cat_alias=NULL){
        $cat = !empty($cat_alias) ? $cat_alias : Request::initial()->param('cat_alias');
        $uri = Route::get($cat ? 'board_cat' : 'board_city')->uri(array(
            'cat_alias' => $cat,
            'city_alias' => $alias,
        ));
        return $uri;
    }

    /**
     * Counts all ads in region cities
     * Return double items array
     * array(
     *      'all' => ... all cities pairs city_id=>count,
     *      'big' => ... only big cities pairs city_id=>count (> $big_limit)
     * )
     * @param int|null $region_id
     * @param int|null $category_id
     * @param int $big_limit
     * @return array
     * @throws Kohana_Exception
     */
    public static function regionCounter($region_id=NULL, $category_id=NULL, $big_limit = 100){
        $regions = array(
            'big' => array(),
            'all' => array(),
        );
        if($region_id > 0)
            $sql = DB::select(array('city_id', 'cit_id'), array(DB::expr('count(*)'), 'cnt'))->from( ORM::factory('BoardAd')->table_name() )->where('pcity_id', '=', $region_id);
        else
            $sql = DB::select(array('pcity_id', 'cit_id'), array(DB::expr('count(*)'), 'cnt'))->from( ORM::factory('BoardAd')->table_name() );
        if($category_id)
            $sql->and_where((Model_BoardCategory::getField('parent_id', $category_id) ? '' : 'p') . 'category_id', '=', $category_id);
        $_ads_count = $sql->group_by('cit_id')->order_by('cnt', 'DESC')->cached(Date::HOUR)->execute()->as_array('cit_id', 'cnt');
        $_childs = ORM::factory('BoardCity')->where('parent_id', '=', $region_id ? $region_id : 0)->order_by('name', 'ASC')->cached(Date::MONTH)->find_all()->as_array('id','name');
        foreach($_childs as $_city_id=>$_city){
            if(isset($_ads_count[ $_city_id ])){
                $regions['all'][$_city_id] = $_ads_count[ $_city_id ];
                if($_ads_count[ $_city_id ] > $big_limit)
                    $regions['big'][$_city_id] = $_ads_count[ $_city_id ];
            }
        }
        return $regions;
    }

    /**
     * Добавить категорию
     * @param $cities
     * @param $row
     * @param $parent_id
     */
    public static function import_city(&$cities, $row, $parent_id = 0){
        /**
         * @var ORM_MPTT $new
         */
        $new = ORM::factory('BoardCity')->values(array(
            'id' => $row['id'],
            'name' => $row['city_name'],
//            'name_in' => $row['city_where'],
//            'name_of' => $row['city_of'],
            'alias' => $row['en_city_name'],
        ));
        if($row['parent']){
            /**
             * @var ORM_MPTT $parent
             */
            $parent = ORM::factory('BoardCity', $parent_id);
            $new->insert_as_last_child($parent);
        }
        else{
//            $parent = ORM::factory('BoardCity', 10000);
//            if(!$parent->loaded())
//                throw new Kohana_Exception('Parent model not found');
//            $new->insert_as_last_child($parent);
            $new->make_root();
        }
        $new->id = $row['id'];
        $new->save();
        if(isset($cities[$row['id']]) && count($cities[$row['id']])){
            foreach($cities[$row['id']] as $_row)
                self::import_city($cities, $_row, $new->id);
        }
    }

    /**
     * Request module parts links array for sitemap generation
     * @return array
     */
    public function sitemapRegions(){
        $links = array();
        $regions = Model_BoardCity::getCitiesList();
        foreach($regions as $region)
            $links[] = $region->getUri();
        return $links;
    }

    /**
     * Fill other model names if empty
     * @return $this
     */
    public function fillNames(){
        if(empty($this->name_in))
            $this->name_in = $this->name;
        if(empty($this->name_of))
            $this->name_of = $this->name;
        return $this;
    }
}