<?php

class Model_BoardAdphoto extends ORM{

    CONST FIRST_LEVEL_FOLDER_CHARS = 3;
    CONST SECOND_LEVEL_FOLDER_CHARS = 2;

    CONST IMAGES_QUALITY = 70; // from 0 ot 100
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
            'name'       => 'Имя',
            'host'       => 'Хост',
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
        $image->image_set_max_edges( BoardConfig::instance()->image_max_width );
        $this->width = $image->width;
        $this->height = $image->height;
        if(file_exists(APPPATH.'data/watermark.png'))
            $mark = Image::factory(APPPATH.'data/watermark.png');
        else
            $mark = Image::factory(MODPATH.'board/data/watermark.png');
        $image->smart_watermark($mark, Image::WATERMARK_BOTTOM_LEFT, 50);
        $image->save($this->getPhoto(true), self::IMAGES_QUALITY);
        chmod($this->getPhoto(true), 0666);
    }

    public function savePhotoImagick($file){
        if(!$this->loaded() || !is_file($file))
            return;
        $this->ext = 'jpg';
        if(file_exists(APPPATH.'data/watermark.png'))
            $mark = APPPATH.'data/watermark.png';
        else
            $mark = MODPATH.'board/data/watermark.png';
        exec('convert '.$file.' -resize '.BoardConfig::instance()->image_max_width.'x'.BoardConfig::instance()->image_max_height.' -quality '.self::IMAGES_QUALITY.' miff:- | composite -dissolve 30% -gravity southwest -geometry +20+20 '.$mark.' - '.$this->getPhoto(true));
    }

    public function saveThumb($file){
        if(!$this->loaded() || !is_file($file))
            return;
        $image = Image::factory($file);
        if(!$this->ext)
            $this->ext = $image->findExtension();
        $image->image_fixed_resize(BoardConfig::instance()->thumb_width, BoardConfig::instance()->thumb_height);
        $image->save($this->getThumb(true), self::IMAGES_QUALITY);
        chmod($this->getThumb(true), 0666);
    }

    public function saveThumbImagick($file){
        if(!$this->loaded() || !is_file($file))
            return;

	    $this->ext = 'jpg';
	    $size = BoardConfig::instance()->thumb_width.'x'.BoardConfig::instance()->thumb_height;
	    exec('convert '.$file.' -thumbnail '.$size.'^ -quality '.self::IMAGES_QUALITY.' -gravity center -extent '.$size.' '.$this->getThumb(true));
    }

    public function getPhoto($getName = false){
        $file = DOCROOT . DIRECTORY_SEPARATOR . $this->getPath() . $this->getName();
        if($getName===TRUE || is_file($file))
            return $file;
        return;
    }
    public function getThumb($getName = false){
        $file = DOCROOT . DIRECTORY_SEPARATOR . $this->getPath() . $this->getName('thumb');
        if($getName===TRUE || is_file($file))
            return $file;
        return;
    }

    public function getPath(){
        $path = "media/upload/board/";
        if(!empty($this->name)){
            $md5 = md5($this->name);
            $first_level = substr($md5, 0, self::FIRST_LEVEL_FOLDER_CHARS);
            $second_level = substr($md5, self::FIRST_LEVEL_FOLDER_CHARS, self::SECOND_LEVEL_FOLDER_CHARS);
            if(!is_dir(DOCROOT . DIRECTORY_SEPARATOR . $path . $first_level))
                mkdir(DOCROOT . DIRECTORY_SEPARATOR . $path . $first_level, 0755);
            $path .= $first_level . DIRECTORY_SEPARATOR;
            if(!is_dir(DOCROOT . DIRECTORY_SEPARATOR . $path . $second_level))
                mkdir(DOCROOT . DIRECTORY_SEPARATOR . $path . $second_level, 0755);
            $path .= $second_level . DIRECTORY_SEPARATOR ;
        }
        return $path;
    }

    public function getName($suffix = NULL){
        $name = $this->name;
        $name .= '_'.$this->ad_id;
        $name .= '_'.$this->id;
        if(!is_null($suffix))
            $name .= '_'.$suffix;
        $name .= '.'.$this->ext;
        return $name;
    }

    public function getPhotoUri(){
//        $base = KoMS::config()->project['protocol'] == 'https' ? '//'.$_SERVER['HTTP_HOST'].'/' : Kohana::$base_url;
        $base = $_SERVER['SERVER_PORT'] == 443 ? '//'.$_SERVER['HTTP_HOST'].'/' : Kohana::$base_url;
        if(is_file($this->getPath() . $this->getName()))
            return $base . $this->getPath() . $this->getName();
        return NULL;
    }

    public function getThumbUri(){
//        $base = KoMS::config()->project['protocol'] == 'https' ? '//'.$_SERVER['HTTP_HOST'].'/' : Kohana::$base_url;
        $base = $_SERVER['SERVER_PORT'] == 443 ? '//'.$_SERVER['HTTP_HOST'].'/' : Kohana::$base_url;
        if(is_file($this->getPath() . $this->getName('thumb')))
            return $base . $this->getPath() . $this->getName('thumb');
        return NULL;
    }

    public function getPhotoTag($alt = '', Array $attributes = array()){
        $attributes['src'] = $this->getPhotoUri();
        $attributes['alt'] = $alt;
//        $attributes['title'] = $alt;
        if($attributes['src'])
            return "<img ".HTML::attributes($attributes).">";
        return NULL;
    }

    public function getThumbTag($alt='', Array $attributes = array()){
        $attributes['src'] = $this->getThumbUri();
        $attributes['alt'] = $alt;
//        $attributes['title'] = $alt;
        if($attributes['src'])
            return "<img ".HTML::attributes($attributes).">";
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
     * Find list of photos by requested AD ids
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