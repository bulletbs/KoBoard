<?php defined('SYSPATH') or die('No direct script access');

/**
 * Model of board xml import statistics
 * Class Model_BoardSearch
 */
class Model_BoardXmlPhoto extends ORM{

    protected $_table_name = 'ad_import_photos';

	protected $_belongs_to = array(
		'ad' => array(
			'model' => 'BoardAd',
			'foreign_key' => 'ad_id',
		),
		'stat' => array(
			'model' => 'BoardXml',
			'foreign_key' => 'stat_id',
		),
	);

    public function labels(){
        return array(
            'id'            => 'Id',
            'url'           => 'URL',
            'ad_id'         => 'Ad record',
            'stat_id'       => 'Stat record',
        );
    }

    /**
     * Upload photo
     * @return bool
     */
    public function uploadPhoto(){
        $res = false;
        if(strpos($this->url, 'http://') !== false){
//            $ad = $this->ad->find();
            $ad = $this->ad;
            if(!$ad->loaded())
                return false;

            $path = DOCROOT . DIRECTORY_SEPARATOR . "media/upload/board/temp";
            if(!is_dir($path))
                mkdir($path, 0755);
            $path .= DIRECTORY_SEPARATOR . md5($this->url.time());
            if(file_put_contents($path, file_get_contents($this->url)))
            $res = $ad->addPhotoImagick($path, false);
            unlink($path);
        }
        $this->delete();
        return $res;
    }
}