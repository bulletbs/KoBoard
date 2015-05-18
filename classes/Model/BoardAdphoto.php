<?php

class Model_BoardAdphoto extends ORM{

    protected $_table_name = 'ad_photos';

	protected $_belongs_to = array(
        'ad' => array(
            'model' => 'BoardAd',
            'foreign_key' => 'ad_id',
        ),
    );

    public function labels(){
        return array(
            'id'        => 'Id',
            'ad_id'     => 'Товар',
            'width'     => 'Ширина',
            'height'    => 'Высота',
            'ext'       => 'Расширение',
        );
    }

    public function delete(){
        if($this->getPhoto())
            unlink($this->getPhoto());
        if($this->getThumb())
            unlink($this->getThumb());
        parent::delete();
    }

    public function savePhoto($file){
        if(!$this->loaded() || !is_file($file))
            return;
        $image = Image::factory($file);
        if(!$this->ext)
            $this->ext = $image->findExtension();
        $image->image_set_max_edges(800);
        $this->width = $image->width;
        $this->height = $image->height;
        $image->save($this->getPhoto(true));
    }

    public function saveThumb($file){
        if(!$this->loaded() || !is_file($file))
            return;
        $image = Image::factory($file);
        if(!$this->ext)
            $this->ext = $image->findExtension();
        $image->resize(100);
        $image->save($this->getThumb(true));
    }

    public function getPhoto($getName = false){
        if($getName===TRUE || is_file($this->getPhotoPath() . $this->id .'.'.$this->ext))
            return $this->getPhotoPath() . $this->id .'.'.$this->ext;
        return;
    }
    public function getThumb($getName = false){
        if($getName===TRUE || is_file($this->getThumbPath() . $this->id .'_thumb.'.$this->ext))
            return $this->getThumbPath() . $this->id .'_thumb.'.$this->ext;
        return;
    }

    public function getPhotoPath(){
        return DOCROOT . "/media/upload/board/";
    }

    public function getThumbPath(){
        return DOCROOT . "/media/upload/board/";
    }

    public function getPhotoUri(){
        if(is_file($this->getThumbPath() . $this->id .'.'.$this->ext))
            return Kohana::$base_url."media/upload/board/" . $this->id . '.' . $this->ext;
        return NULL;
    }

    public function getThumbUri(){
        if(is_file($this->getThumbPath() . $this->id .'_thumb.'.$this->ext))
            return Kohana::$base_url."media/upload/board/" . $this->id . '_thumb.' . $this->ext;
        return NULL;
    }

    public function getPhotoTag($alt = '', Array $attributes = array()){
        $photo = $this->getPhotoUri();
        if($photo)
            return HTML::image($photo, Arr::merge(array(
                'alt'=>$alt,
                'title'=>$alt,
            ), $attributes));
        return NULL;
    }

    public function getThumbTag($alt='', Array $attributes = array()){
        $photo = $this->getThumbUri();
        if($photo)
            return HTML::image($photo, Arr::merge(array(
                'alt'=>$alt,
                'title'=>$alt,
            ), $attributes));
        return NULL;
    }



    /**
     * Find list of photos by requested ads ids
     * @param array $ids - Ads IDs array
     * @return array|object - Photos objects array
     */
    public static function adsPhotoList(Array $ids){
        $photos = array();
        if(count($ids)){
            $db_photos = DB::select()
                ->distinct('ad_id')
                ->from(ORM::factory('BoardAdphoto')->table_name())
                ->where('ad_id', 'IN', $ids)
                ->and_where('main', '=', 1)
                ->as_object('Model_BoardAdphoto')
                ->execute();
            ;
            foreach($db_photos as $photo)
                $photos[$photo->ad_id] = $photo;
        }
        return $photos;
    }


    /**
     * Find list of photos by requested ads ids
     * @param array $ids - Ads IDs array
     * @return array|object - Photos objects array
     */
    public static function adsFullPhotoList(Array $ids){
        $photos = array();
        if(count($ids)){
            $db_photos = DB::select()
                ->distinct('ad_id')
                ->from(ORM::factory('BoardAdphoto')->table_name())
                ->where('ad_id', 'IN', $ids)
                ->as_object('Model_BoardAdphoto')
                ->execute();
            ;
            foreach($db_photos as $photo)
                $photos[$photo->ad_id][] = $photo;
        }
        return $photos;
    }



}