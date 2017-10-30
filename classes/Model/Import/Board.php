<?php

abstract class Model_Import_Board{

	protected $_feed_type = 0;
    protected $_upload_feed;

	protected $_tmp_files = array();

	protected $_stats = array(
		'total' => 0,
		'imported' => 0,
		'errors' => 0,
	);
	protected $_errors = array();

	public $_validated;

	public $stat_id = 0;

	public $limits = array(
		'limit' => 0,
		'exist' => 0,
		'last' => 0,
	);
	public $contacts = array(
		'name' => '',
		'email' => '',
		'phone' => '',
		'user_id' => 0,
		'company_id' => 0,
	);

    /**
     * Model_Import_Board constructor.
     */
    public function __construct(){

    }

	/**
	 * Set upload feed
	 * @param $upload - feed file
	 * @param $type - type of feed (unknown/realty/auto)
	 * @return $this
	 */
	public function setupUpload($upload){
		$feed = simplexml_load_file($upload);
		if(isset($feed->offer)){
			$this->_validated = true;
			$this->_upload_feed = $feed;
		}
		return $this;
	}

	public function stats(){
		return array(
			'type' => $this->_feed_type,
			'cnt' => $this->_stats['total'],
			'cnt_success' => $this->_stats['imported'],
		);
	}

	/**
	 * Return runtime errors list
	 * @return array
	 */
	public function errors(){
		return $this->_errors;
	}


	/**
	 * Парсинг фида
	 */
	public function walkFeed(){
		if($this->_validated){
			$this->_stats['total'] = count($this->_upload_feed->offer);
			foreach($this->_upload_feed->offer as $offer){
				/* Check import limits */
				if( $this->limits['last'] <= 0){
//					$this->errors[] = ('Import limit exceeded (:limit)', array(':limit'=>$this->limits['limit']));
					Flash::warning(__('Import limit exceeded (:limit)', array(':limit'=>$this->limits['limit'])));
					break;
				}
				$ad = $this->parseItem($offer);
				try{
					$ad->save();
					$this->_saveImages($offer, $ad);
                    $this->_saveParams($offer, $ad);
					$this->increaseLimit();
				}
				catch(ORM_Validation_Exception $e){
					$this->_errors[$offer->attributes()->{'internal-id'}] = $e->errors('validation');
					$this->_stats['errors']++;
				}
			}
			$this->_clearTemp();
			Flash::success(__(':imported rows of :total was imported (errors :errors)', array(
				':total' => $this->_stats['total'],
				':imported' => $this->_stats['imported'],
				':errors' => $this->_stats['errors'],
			)));
		}
	}

	public function parseItem($item){
		$internal_id = (int) $item->attributes()->{'internal-id'};
		$ad = ORM::factory('BoardAd')->where('user_id','=',Auth::instance()->get_user()->id)->and_where('external_id','=',$internal_id)->find();
		if(!$ad->loaded())
			$ad = ORM::factory('BoardAd')->values(array(
				'external_id' => (int) $item->attributes()->{'internal-id'},
				'publish' => 1,
			));
		$ad->title = $this->_generateTitle($item);
		$ad->description = $item->description;

		$ad->values($this->_parsePrice($item));
		$ad->values($this->_parseContacts($item));
		$ad->values($this->_parseRegion($item));
		$ad->values($this->_parseCategory($item));
		return $ad;
	}


	protected function _saveParams($item, $model){

	}

	/**
	 * Сохранение изображений
	 * @param $item
	 * @param $model
	 * @param string $type
	 */
	protected function _saveImages($item, $model){
		if(isset($item->image)){
			$images = (array) $item->image;
			if($model->photo_count != count($images)){
				foreach($images as $_image)
					ORM::factory('BoardXmlPhoto')->values(array(
						'url' => $_image,
						'ad_id' => $model->id,
						'stat_id' => $this->stat_id,
					))->save();
			}
		}
	}

	protected function _OLD__saveImages($item, $model, $type='realty'){
		if(isset($item->image)){
			$images = (array) $item->image;
			if($model->photo_count != count($images)){
				if($model->photo_count > 0){
					$photos = $model->photos->find_all();
					foreach($photos as $_photo)
						$_photo->delete();
				}
				foreach($images as $_image){
					$_image = $this->_downloadImage((string) $_image);
					$model->addPhoto($_image, false);
				}
				$model->countPhotos();
				$model->setMainPhoto();
			}
		}
	}


	/**
	 * Определение региона
	 * @param $item
	 * @return array
	 * @throws Kohana_Exception
	 */
	protected function _parseRegion($item){
		$result = array();
		if(isset($item->location)){
			$city = ORM::factory('BoardCity')->where('name','=',$item->location->{'locality-name'})->cached(Date::MONTH)->find();
			if($city->loaded()){
				$result['pcity_id'] = $city->parent_id;
				$result['city_id'] = $city->id;
			}
		}
		return $result;
	}

	/**
	 * Определение категории
	 * @param $item
	 * @return array
	 * @throws Kohana_Exception
	 */
	protected function _parseCategory($item){
		return array(
			'category_id' => 0,
			'pcategory_id' => 0,
		);
	}

	/**
	 * Определение контактных данных
	 * @param $item
	 * @param string $type
	 * @return array
	 */
	protected function _parseContacts($item, $type='realty'){
		$contacts = array();
		if(isset($item->{'sales-agent'})){
			$contacts['email'] = isset($item->{'sales-agent'}->email) ? (string) $item->{'sales-agent'}->email : $this->contacts['email'];
			$contacts['name'] = isset($item->{'sales-agent'}->name) ? (string) $item->{'sales-agent'}->name : $this->contacts['name'];
			$contacts['phone'] = isset($item->{'sales-agent'}->phone) ? (string) $item->{'sales-agent'}->phone : $this->contacts['phone'];
			$contacts['address'] = isset($item->location->address) ? (string) $item->location->address : '';
		}
		else{
			$contacts = Arr::merge($contacts, Arr::extract($this->contacts, array('name', 'email', 'phone')));
		}
		$contacts = Arr::merge($contacts, Arr::extract($this->contacts, array('user_id', 'company_id')));
		return $contacts;
	}

	/**
	 * @param $item
	 * @return array
	 */
	protected function _parsePrice($item){
		$price = array();
		if(isset($item->price)){
			$price_units = array_flip(BoardConfig::instance()->price_units['iso']);
			$currency = isset($item->price->currency) ? (string) $item->price->currency : BoardConfig::instance()->price_units['iso']['unit_0'];
			$price['price'] = (int) $item->price->value;
			$price['price_unit'] =  isset($price_units[$currency]) ? str_replace('unit_','',$price_units[$currency]) : 0;
		}
		return $price;
	}

	/**
	 * Генерация заголовка объявления
	 * @param $item
	 * @param string $type
	 * @return string
	 */
	protected function _generateTitle($item, $type = 'realty'){
		$title = '';
		if(isset($item->type) && isset($item->{'category'}))
			switch($type) {
				case 'realty':
					$title = $item->type == 'продажа' ? 'Продается' : 'Сдается';
					$title .= ' '. mb_strtolower($item->{'category'});
					$title .= ', '. $item->location->{'locality-name'};
					$title .= ' (#'. (int) $item->attributes()->{'internal-id'} .')';
					break;
			}
		return $title;
	}

	/**
	 * Download HTTP base image to local folder
	 * @param $url
	 * @return bool|string
	 */
	protected function _downloadImage($url){
		$file = FALSE;
		if(strpos($url, 'http://') !== false){
			$path = DOCROOT . DIRECTORY_SEPARATOR . "media/upload/board/temp";
			if(!is_dir($path))
				mkdir($path, 0755);
			$path .= DIRECTORY_SEPARATOR . md5($url.time());
			if(file_put_contents($path, file_get_contents($url)))
				$file = $path;
			$this->_tmp_files[] = $path;
		}
		return $file;
	}

	/**
	 * Clear temporary folder
	 */
	protected function _clearTemp(){
		foreach ($this->_tmp_files as $tmp_file) {
			unlink($tmp_file);
		}
	}

	/**
	 * Icrease exist counter
	 * and decrease last counter
	 */
	protected function increaseLimit(){
		$this->limits['exist']++;
		$this->limits['last']--;
		$this->_stats['imported']++;
	}

}