<?php

class Model_Import_BoardUnknown extends Model_Import_Board{

	public function setupUpload( $upload_source ) {
		$this->upload_feed = file_get_contents($upload_source);
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