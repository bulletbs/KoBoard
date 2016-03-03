<?php defined('SYSPATH') or die('No direct script access');

/**
 * Model of board xml import statistics
 * Class Model_BoardSearch
 */
class Model_CatalogXml extends ORM{

    protected $_table_name = 'ad_import_stat';

    protected $upload_feed;

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
     * Set upload feed
     * @param $upload
     * @return $this
     */
    public function setupUpload($upload){
        $this->upload_feed = file_get_contents($upload);

        return $this;
    }


    public function parseFeed(){
        $feed = Kohana_Feed::parse($this->upload_feed);

        return $feed;
    }

    public function parseItem($item){
        $ad = ORM::factory('BoardAd');

        return $ad;
    }
}