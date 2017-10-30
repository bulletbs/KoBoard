<?php

class Model_Import_BoardRealty extends Model_Import_Board{

	// Realty type
	protected $_feed_type = 1;

	protected $_filters;

	/**
	 * Определение категории
	 * @param $item
	 * @return array
	 * @throws Kohana_Exception
	 */
	protected function _parseCategory($item){
		$result = array();
		if(isset($item->{'property-type'})){
			$categories = $this->_realtyCategories();
			$category_name = Text::mb_ucfirst($item->type).' '.$categories[mb_strtolower($item->category)];
			$category = ORM::factory('BoardCategory')->where('name','=',$category_name)->find();
			if($category->loaded()){
				$result['pcategory_id'] = $category->parent_id;
				$result['category_id'] = $category->id;
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
	protected function _saveParams($item, $model){

		$values = array();
		$filters = $this->_realtyFilters($model->category_id);

		foreach ($filters as $filter_id=>$filter){
			switch($filter['name']){
				case 'Тип дома':
					if(array_search('Продажа домов в городе', $filter['options']))
						$values[$filter_id] = array_search('Продажа домов в городе', $filter['options']);
					break;

				case 'Тип квартиры':
					if(isset($item->{'new-flat'}) && array_search('Новостройки', $filter['options']))
						$values[$filter_id] = array_search('Новостройки', $filter['options']);
					elseif (!isset($item->{'new-flat'}) && array_search('Вторичный рынок', $filter['options']))
						$values[$filter_id] = array_search('Вторичный рынок', $filter['options']);
					break;

				case 'Тип аренды':
					switch($model->category_id){
						case 15: // Дома
							if(isset($item->{'price'}) && $item->{'price'}->{'period'} == 'месяц' && array_search('Долгосрочная аренда домов', $filter['options']))
								$values[$filter_id] = array_search('Долгосрочная аренда домов', $filter['options']);
							if(isset($item->{'price'}) && ($item->{'price'}->{'period'} == 'сутки' || $item->{'price'}->{'period'} == 'час') && array_search('Дома посуточно, почасово', $filter['options']))
								$values[$filter_id] = array_search('Дома посуточно, почасово', $filter['options']);
							break;
						case 14: // Комнаты
							if(isset($item->{'price'}) && $item->{'price'}->{'period'} == 'месяц' && array_search('Долгосрочная аренда комнат', $filter['options']))
								$values[$filter_id] = array_search('Долгосрочная аренда комнат', $filter['options']);
							if(isset($item->{'price'}) && $item->{'price'}->{'period'} == 'сутки' && array_search('Комнаты посуточно', $filter['options']))
								$values[$filter_id] = array_search('Комнаты посуточно', $filter['options']);
							break;
						case 13: // Квартиры
							if(isset($item->{'price'}) && $item->{'price'}->{'period'} == 'месяц' && array_search('Долгосрочная аренда квартир', $filter['options']))
								$values[$filter_id] = array_search('Долгосрочная аренда квартир', $filter['options']);
							if(isset($item->{'price'}) && $item->{'price'}->{'period'} == 'сутки' && array_search('Квартиры посуточно', $filter['options']))
								$values[$filter_id] = array_search('Квартиры посуточно', $filter['options']);
							if(isset($item->{'price'}) && $item->{'price'}->{'period'} == 'час' && array_search('Квартиры с почасовой оплатой', $filter['options']))
								$values[$filter_id] = array_search('Квартиры с почасовой оплатой', $filter['options']);
							break;
					}
					break;

				case 'Площадь':
				case 'Площадь дома':
					if(!empty((string)$item->{'area'}))
						$values[$filter_id] = (string) $item->{'area'}->{'value'};
					break;

				case 'Площадь участка':
					if(isset($item->{'lot-area'}) && $item->{'lot-area'}->{'unit'} == 'сот')
						$values[$filter_id] = (string) $item->{'lot-area'}->{'value'};
					break;

				case 'Жилая площадь':
					if(!empty((string)$item->{'living-space'}))
						$values[$filter_id] = (string) $item->{'living-space'}->{'value'};
					break;

				case 'Этаж':
					if(!empty((string)$item->{'floor'}))
						$values[$filter_id] = (string) $item->{'floor'};
					break;

				case 'Количество комнат':
				case 'Всего комнат в квартире':
					if(!empty((string)$item->{'rooms'}))
						$values[$filter_id] = (string) $item->{'rooms'};
					break;
			}
		}
//		echo Debug::vars($filters);
//		echo Debug::vars($values);
//		die();

		$model->saveFilters($values);
	}

	/**
	 * Список сопоставления категорий Яндекс к нашим
	 * @return array
	 */
	protected function _realtyCategories(){
		return array(
			'квартира' => 'квартир',
			'дача' => 'домов',
			'коттедж' => 'домов',
			'cottage' => 'домов',
			'дом' => 'домов',
			'house' => 'домов',
			'дом с участком' => 'домов',
			'house with lot' => 'домов',
			'часть дома' => 'домов',
			'flat' => 'квартир',
			'комната' => 'комнат',
			'room' => 'комнат',
			'таунхаус' => 'домов',
			'townhouse' => 'домов',
			'участок' => 'земли',
			'lot' => 'земли',
			'коммерческая' => 'помещений',
			'commercial' => 'помещений',
		);
	}

	/**
	 * Load Realty filters list
	 * @param null $category_id
	 *
	 * @return null
	 */
	protected function _realtyFilters($category_id = NULL){
		if(is_null($this->_filters)){
			// Недвижимость
			$parent = ORM::factory('BoardCategory')->where('parent_id','=','0')->and_where('name','=','Недвижимость')->find();

			$rows = DB::select('id','name')
			  ->from('ad_categories')
			  ->where('parent_id','=', $parent->id)
			  ->as_object('Model_BoardCategory')
			  ->execute()
			  ->as_array('id')
			;
			foreach ($rows as $row_id=>$row){
				$this->_filters[$row_id] = Model_BoardFilter::loadFiltersByCategory($row);
			}
		}

		if(!is_null($category_id))
			return isset($this->_filters[$category_id]) ? $this->_filters[$category_id] : NULL;
		return $this->_filters;
	}
}