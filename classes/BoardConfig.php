<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Board config-loader / parameter-getter
 * Class BoardConfig
 */
class BoardConfig {

    CONST CONFIG_FILE_NAME = 'board';

    protected static $_instance;

    protected $config;

    protected $_price_unit_options = array();

    /**
     * Creates config instance
     * @return BoardConfig
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
        $this->_config = Kohana::$config->load( self::CONFIG_FILE_NAME )->as_array();
    }

    /**
     * Closed method
     */
    private function __clone(){}

    /**
     * Look for price template
     * @param $unit - price unit index
     * @return string
     */
    public function priceTemplate($unit){
        if(isset($this->_config['price_units']['templates']['unit_'.$unit]))
            return $this->_config['price_units']['templates']['unit_'.$unit];
        return '<price>';
    }

    /**
     * Replaces options keys by INTEGER keys
     * @return array
     */
    public function unitsOptions(){
        if(!count($this->_price_unit_options)){
            $options = $this->_config['price_units']['options'];
            foreach($options as $key => $value)
                $this->_price_unit_options[ str_replace('unit_', '', $key) ] = $value;
        }
        return $this->_price_unit_options;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function priceUnitName($id = 0){
        return $this->_config['price_units']['options']['unit_'.$id];
    }

    /**
     * @return mixed
     */
    public function priceHints(){
        return $this->_config['price_hints'];
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
	 * Return values array
	 * by given key=>value_key pairs

	 * input array:
	  array(
		'h1'=>'h1_title',
		'h2'=>'h2_title',
	  )

	 * output array:
	  array(
		'h1'=>'Title for H1',
		'h2'=>'Title for H2',
	  )

	 * @param $keys
	 * @return array
	 */
    public function getValuesArray($keys){
    	$result = array();
    	foreach ($keys as $key=>$value_key){
    		if(isset($this->_config[$value_key]))
    			$result[$key] = $this->_config[$value_key];
	    }
	    return $result;
    }
}