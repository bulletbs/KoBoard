<?php

class Model_Import_BoardRealty extends Model_Import_Board{

    public function parseFeed(){
        $feed = Kohana_Feed::parse($this->upload_feed);
        return $feed;
    }

    public function parseItem($item){
        $ad = ORM::factory('BoardAd');
        return $ad;
    }
}