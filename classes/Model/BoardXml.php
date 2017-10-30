<?php defined('SYSPATH') or die('No direct script access');

/**
 * Model of board xml import statistics
 * Class Model_BoardSearch
 */
class Model_BoardXml extends ORM{

    protected $_table_name = 'ad_import_stats';
    protected $_handler;

    public $types = array(
    	'none',
    	'realty',
    );

    /**
     * @return array
     */
    public function labels(){
        return array(
            'id'            => 'Id',
            'addtime'          => 'Время',
            'category_id'   => 'Категория',
            'cnt'           => 'Запросов',
        );
    }

	/**
	 * Model_BoardXml constructor.
	 *
	 * @param null $id
	 */
    public function __construct($id = NULL) {
    	return parent::__construct($id);
    }

	/**
	 * Set handler from factory
	 * @param $file
	 * @param $type
	 *
	 * @return $this
	 */
    public function addHandler($file, $type){
    	$this->_handler = Model_Import_Factory::instance($type)->setupUpload($file);
    	return $this;
    }

	/**
	 * Setup import handler contacts
	 * @param array $contacts
	 *
	 * @return $this
	 */
    public function setHandlerContacts(Array $contacts){
        $this->_handler->contacts = Arr::merge($this->_handler->contacts, $contacts);
	    return $this;
    }

	/**
	 * Setup import handler limits
	 * @param array $limits
	 * array(
		'exist' => 5,
		'limit' => 500,
	 *
	 * @return $this
	 */
	public function setHandlerLimits(Array $limits){
		$this->_handler->limits = Arr::merge($this->_handler->limits, $limits);
		return $this;
	}

	/**
	 * Add tish stat ID to handler
	 * @return $this
	 */
	public function setHandlerStatId(){
		$this->_handler->stat_id = $this->id;
		return $this;
	}

	/**
	 * Handler errors
	 * @return mixed
	 */
	public function getHandlerErrors(){
		return $this->_handler->errors();
	}

	/**
	 * Executes import
	 */
	public function execute(){
		$this->values(array(
			'user_id' => Auth::instance()->get_user()->id,
			'addtime' => time(),
		))->save();
		$this->setHandlerStatId();
	    $this->_handler->walkFeed();
		$this->values( $this->_handler->stats() )->save();
	}

	/**
	 * Check if feed valid
	 * @return bool
	 */
    public function isValid(){
        return $this->_handler->_validated == true;
    }
}