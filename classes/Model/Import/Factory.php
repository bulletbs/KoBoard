<?php
/**
 * Created by PhpStorm.
 * User: bullet
 * Date: 25.10.17
 * Time: 16:19
 */

class Model_Import_Factory {

	/**
	 * @param string $type
	 *
	 * @return Model_Import_BoardRealty|Model_Import_BoardUnknown
	 */
	public static function instance($type = 'realty'){
		switch($type){
			case 'realty':
				return new Model_Import_BoardRealty;
				break;
//			case 'auto':
//				return new Model_Import_BoardAuto;
//				break;
			default:
				return new Model_Import_BoardUnknown;
				break;
		}
	}
}