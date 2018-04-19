<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Board search pages caching class
 * Class BoardCache
 */
class BoardCache {

    CONST CACHE_SUBFOLDER = 'boardsearch';

        protected static $_instance;
        protected $cache_folder = '';


    /**
     * Creates config instance
     * @return BoardCache
     */
    public static function instance(){
        if(is_null(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructs a new config board instance
     */
    public function __construct()
    {
    	$this->cache_folder = DOCROOT. '/application/cache/' . BoardCache::CACHE_SUBFOLDER . '/';
    	if(!file_exists($this->cache_folder))
    		mkdir($this->cache_folder, 0775);
    }

	/**
	 * Write data to cache
	 * @param $params
	 * @param $data
	 *
	 * @return bool
	 */
	public function writeData($params, $data){
		$profilertoken = Profiler::start('boardcache', 'Board Cache Write');
		$path = BoardCache::filenameByParams($params);
		$save_path = $this->cache_folder . $path;
		$hash = substr(md5($save_path), 0, 2);
		if(!file_exists($this->cache_folder . $hash))
			mkdir($this->cache_folder . $hash, 0775);
		$save_path = $this->cache_folder . $hash .'/' . $path;
		if (!$fp = fopen($save_path, 'w')) return false;
		$retries = 0;
		$max_retries = 10;
		do {if ($retries > 0)usleep(500000);$retries += 1;}
		while (!flock($fp, LOCK_EX) and $retries <= $max_retries);
		if ($retries >= $max_retries) return false;
		fwrite($fp, serialize($data));
		flock($fp, LOCK_UN);
		fclose($fp);
		Profiler::stop($profilertoken);
		return true;
	}

	/**
	 * Read cached data
	 * @param $params
	 * @param int $expiry
	 *
	 * @return bool|mixed
	 */
	public function readData($params, $expiry=3600){
		$result = false;
		$profilertoken = Profiler::start('boardcache', 'Board Cache Read');
		$path = BoardCache::filenameByParams($params);
		$read_path = $this->cache_folder . $path;
		$hash = substr(md5($read_path), 0, 2);
		$read_path = $this->cache_folder . $hash .'/' . $path;

		if (file_exists($read_path) && (time() - $expiry) <= filemtime($read_path))
			$result = unserialize(file_get_contents($read_path));
		Profiler::stop($profilertoken);
		return $result;
	}

	/**
	 * Clean requested cache data
	 * @param $template
	 */
	public function cleanData($template){
		$files = $this->recursive_search( $this->cache_folder . $template, GLOB_NOSORT);
		foreach($files as $_file){
			if(file_exists($_file))
				unlink($_file);
		}
	}

	/**
	 * Recursive Search
	 * @param $pattern
	 * @param int $flags
	 *
	 * @return array
	 */
	public function recursive_search($pattern, $flags = 0){
		if (!function_exists('glob_recursive'))
			return $this->_bc_glob_recursive($pattern, $flags = 0);
		return glob_recursive($pattern, $flags = 0);
	}

	/**
	 * Our glob_recursive() function replacement
	 * @param $pattern
	 * @param int $flags
	 *
	 * @return array
	 */
	protected function _bc_glob_recursive($pattern, $flags = 0)
	{
		$files = array();
		if($res = glob($pattern, $flags))
			$files = $res;
		foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
			$files = array_merge($files, $this->_bc_glob_recursive($dir . '/' . basename($pattern), $flags));
		}
		return $files;
	}

	/**
	 * Generate file name
	 * @param array $params
	 *
	 * @return string
	 */
	public static function filenameByParams(Array $params){
		$path = 'search';
		if(isset($params['city_alias']))
			$path .= '_' . $params['city_alias'];
		if(isset($params['cat_alias']))
			$path .= '_' . $params['cat_alias'];
		$path .= '_p' . (isset($params['page']) ? $params['page'] : 1);
		return $path;
	}

	/**
	 * Очистка кеша списка объявлений в определенной категории и(или) городе(области)
	 * Область очищается автоматически при указании города
	 * Так же очищается кеш родительской рубрики при указании дочерней
	 * @param null $city_id - город
	 * @param null $id_category - категория
	 * @param bool $fullClean - полная очистка, включая область, раздел и все их вариации
	 */
	public static function cleanList($city_id, $id_category, $fullClean = true){
		$profilertoken = Profiler::start('boardcache', 'Board Cache Clean');
		$_cpaths = array();

		$_cpaths[] = 'search_'. Model_BoardCity::getField('alias', $city_id) .'_p';
		$_cpaths[] = 'search_'. Model_BoardCity::getField('alias', $city_id) .'_'. Model_BoardCategory::getField('alias', $id_category);
		$_cpaths[] = 'search_'. BoardConfig::instance()->country_alias .'_'. Model_BoardCategory::getField('alias', $id_category);

		if($fullClean){
			$region_id = Model_BoardCity::getField('parent_id', $city_id);
			$parent_cat = Model_BoardCategory::getField('parent_id', $id_category);
			$_cpaths[] = 'search_'. Model_BoardCity::getField('alias', $region_id) .'_p';
			$_cpaths[] = 'search_'. BoardConfig::instance()->country_alias .'_'. Model_BoardCategory::getField('alias', $parent_cat);
			$_cpaths[] = 'search_'. Model_BoardCity::getField('alias', $city_id) .'_'. Model_BoardCategory::getField('alias', $parent_cat);
			$_cpaths[] = 'search_'. Model_BoardCity::getField('alias', $region_id) .'_'. Model_BoardCategory::getField('alias', $parent_cat);
			$_cpaths[] = 'search_'. Model_BoardCity::getField('alias', $region_id) .'_'. Model_BoardCategory::getField('alias', $id_category);
		}

		// clean paths
		if(count($_cpaths))
			foreach($_cpaths as $_cpath)
				BoardCache::instance()->cleanData($_cpath . '*');
		Profiler::stop($profilertoken);
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