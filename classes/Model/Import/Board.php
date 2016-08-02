<?php

abstract class Model_Import_Board{

    protected $upload_feed;

    /**
     * Model_Import_BoardRealty constructor.
     * @param $upload_source - File or HTTP URL
     */
    public function __construct($upload_source){
        $this->upload_feed = file_get_contents($upload_source);
        return $this;
    }

    abstract public function parseFeed();
    abstract public function parseItem($item);
}