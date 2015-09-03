<?php defined('SYSPATH') or die('No direct script access.');

class Model_BoardAd extends ORM{

    const CACHE_TIME = 3700;

    protected $_table_name = 'ads';

    const PRIVATE_TYPE = 0;
    const BUSINESS_TYPE = 1;
    public static $adType = array(
        'Private',
        'Business',
    );
    public static $jobType = array(
        'Resume',
        'Vacancy',
    );

    protected $_uriToMe;

    protected $_belongs_to = array(
        'user' => array(
            'model' => 'user',
            'foreign_key' => 'user_id',
        ),
        'category' => array(
            'model' => 'BoardCategory',
            'foreign_key' => 'category_id',
        ),
        'city' => array(
            'model' => 'BoardCity',
            'foreign_key' => 'city_id',
        ),
    );
    public $stopwords = 0;

    protected $_has_many = array(
        'filtervalues' => array(
            'model' => 'BoardFiltervalue',
            'foreign_key' => 'ad_id',
        ),
        'photos' => array(
            'model' => 'BoardAdphoto',
            'foreign_key' => 'ad_id',
        ),
    );

    public function rules(){
        return array(
            'title' => array(
                array('not_empty'),
                array(array($this, 'checkStopWords'), array(':validation', ':field')),
                array(array($this, 'checkDuplicates'), array(':validation', ':field')),
                array('min_length', array(':value',3)),
                array('max_length', array(':value',255)),
                array(array($this, 'setModerate'), array(':field')),
            ),
            'description' => array(
                array('not_empty'),
                array(array($this, 'checkStopWords'), array(':validation', ':field')),
                array('max_length', array('value:',1024)),
                array(array($this, 'setModerate'), array(':field')),
            ),
            'price' => array(
                array(array($this, 'checkPrice'), array(':validation', ':field')),
            ),
            'category_id' => array(
                array('not_empty'),
            ),
            'city_id' => array(
                array('not_empty'),
            ),
            'name' => array(
                array('not_empty'),
            ),
//            'email' => array(
//                array('not_empty'),
//                array('email'),
//            ),
//            'address' => array(
//                array('not_empty'),
//            ),
//            'user_id' => array(
//                array('not_empty'),
//            ),
        );
    }

    public function labels(){
        return array(
            'id' => '№',
            'title' => 'Заголовок',
            'addtime' => 'Дата добавления',
            'addTime' => 'Добавлено',
            'type' => 'Куплю / Продам',
            'business' => 'Business',
            'name' => 'Название',
            'price' => 'Цена',
            'user_id' => 'Пользователь',
            'category_id' => 'Категория',
            'filters' => 'Параметры',
            'city_id' => 'Регион',
            'text' => 'Текст',
            'description' => 'Описание',
            'descriptionHide' => 'Описание',
            'video' => 'Видео',
            'photo' => 'Фотографии',
            'name' => 'Имя',
            'email' => 'E-mail',
            'phone' => 'Телефон',
            'address' => 'Адрес',
            'moderated' => 'Проверено',
        );
    }
    /**
     * Ручная проверка данных из POST
     * для внешней валидации данных при добавлении объявлений с созданием пользователей
     * @param array $post
     * @return array
     */
    public function validateData(Array $post){
        $valid = Validation::factory($post);
        $valid->labels($this->labels());
        foreach ($this->rules() as $field => $rules)
            $valid->rules($field, $rules);
        if(!$valid->check())
            return $valid->errors('',TRUE);
        return array();
    }

    /**
     * Добавить фото к объявлению
     * @param $file
     * @return bool
     */
    public function addPhoto( $file ){
        if(!$this->loaded() || !Image::isImage($file))
            return false;
        $photo = ORM::factory('BoardAdphoto')->values(array(
            'ad_id'=>$this->pk(),
            'name'=>Text::transliterate($this->title, true),
        ))->save();
        $photo->savePhoto($file);
        $photo->saveThumb($file);
        $photo->update();
        $this->increasePhotos();
    }

    /**
     * Удалить фото из объхявления
     * @param $file_id
     * @throws Kohana_Exception
     */
    public function deletePhoto($file_id){
        $photo = ORM::factory('BoardAdphoto', $file_id);
        if($photo->loaded()){
            $photo->delete();
            $this->decreasePhotos();
        }
    }

    /**
     * Сохраняем значения фильтров из массива
     * @param $filters
     */
    public function saveFilters($filters){
        DB::delete('ad_filter_values')->where('ad_id', '=', $this->id)->execute();
        if(NULL !== $filters)
            foreach($filters as $id=>$val){
                $value = ORM::factory('BoardFiltervalue')->values(array(
                    'ad_id' => $this->id,
                    'filter_id' => $id,
                    'value' => $val,
                ));
                /* Список опций сохраняем битовой маской */
                if(is_array($val))
                    $value->value = Model_BoardFiltervalue::optlist2bin($val);
                $value->save();
            }
    }

    /**
     * @param Validation $validation
     * @return ORM|void
     */
    public function save(Validation $validation=NULL){
        if(!$this->addtime)
            $this->addtime = time();
        /**
         * Setting parents
         */
        if(!$this->pcity_id && $this->city_id){
            $this->pcity_id = ORM::factory('BoardCity', $this->city_id)->parent_id;
        }
        if(!$this->pcategory_id && $this->category_id){
            $this->pcategory_id = ORM::factory('BoardCategory', $this->category_id)->parent_id;
        }
        return parent::save($validation);
    }

    /**
     * Удалить
     * @return ORM|void
     */
    public function delete(){
        foreach( ORM::factory('BoardAdphoto')->where('ad_id','=',$this->pk())->find_all()  as $photo)
            $photo->delete();
        foreach( ORM::factory('BoardFiltervalue')->where('ad_id','=',$this->pk())->find_all()  as $item)
            $item->delete();
        parent::delete();
    }

    /**
     *
     * @throws Kohana_Exception
     */
    public function refresh(){
        $this->addtime = time();
        if($this->publish == 0)
            $this->publish = 1;
        $this->update();
    }

    /**
     * Flip company status
     */
    public function flipStatus(){
        $this->publish = $this->publish == 0 ? 1 : 0;
        $this->update();
    }

    /**
     * @param null $id
     */
    public function setMainPhoto($id = NULL){
        $photo_table = ORM::factory('BoardAdphoto')->table_name();
        $main = ORM::factory('BoardAdphoto')->where('ad_id' ,'=', $this->id)->and_where('main' ,'=', 1)->find();
        $exists = $main->loaded();
        if($id){
            DB::update($photo_table)->set(array('main'=>0))->where('ad_id' ,'=', $this->id)->execute();
            $exists = DB::update($photo_table)->set(array('main'=>1))->where('ad_id' ,'=', $this->id)->and_where('id' ,'=', $id)->execute();
        }
        if(!$exists){
            $photo = ORM::factory('BoardAdphoto')->where('ad_id' ,'=', $this->id)->find();
            if($photo)
                DB::update($photo_table)->set(array('main'=>1))->where('ad_id' ,'=', $this->id)->and_where('id' ,'=', $photo->id)->execute();
        }
    }

    /**
     * Returns ORM Object with ordering
     * and most useful conditions
     * @return $this
     */
    public static function boardOrmFinder(){
        $table = ORM::factory('BoardAd')->table_name();
        $finder = DB::select($table.'.*')->from($table)
            ->order_by('addtime', 'desc')
            ->as_object(get_class(ORM::factory('BoardAd')))
          ;
        return self::finderConditions($finder);
    }

    /**
     * Returns ORM Object without ordering
     * @return $this
     */
    public static function boardOrmCounter(){
        $counter =  DB::select(DB::expr('count(*) cnt'))
            ->from( ORM::factory('BoardAd')->table_name() )
        ;
        return self::finderConditions($counter);
    }

    /**
     * Add conditions to ORM or DB builder
     * @param ORM | ORM_MPTT | Database_Query_Builder $finder
     * @return ORM | ORM_MPTT
     */
    public static function finderConditions($finder){
        return $finder
            ->where('publish','=','1')
            ;
    }

    /**
     * Add conditions to ORM or DB builder
     * @param ORM | ORM_MPTT | Database_Query_Builder $finder
     * @return mixed
     */
    public static function finderSorting($finder){
        return $finder
            ->order_by('addtime', 'desc')
            ;
    }

    /**
     * Return correct ad site uri
     * @return null|string
     */
    public function getGotoLink(){
        if(!empty($this->site)){
            return URL::base().Route::get('board')->uri(array(
                'action' => 'goto',
                'id' => $this->id,
            ));
        }
        return '#';
    }

    /**
     * Return ad print uri
     * @return string
     */
    public function getPrintLink(){
        return URL::base().Route::get('board_ad_print')->uri(array(
            'city_alias' => Model_BoardCity::getField('alias', $this->city_id),
            'cat_alias' => Model_BoardCategory::getField('alias', $this->category_id),
            'alias' => Text::transliterate($this->title, true),
            'id' => $this->id,
        ));
    }

    /**
     * Redirection to source url
     */
    public function gotoSource(){
        if(!empty($this->site)){
            if(!strstr($this->site, 'http://') && !strstr($this->site, 'https://'))
                $this->site = 'http://'. $this->site;
            header("Location: ". $this->site);
        }
        die();
    }


    /**
     * Generate Ad uri
     * @return string
     */
    public function getUri(){
        if(is_null($this->_uriToMe)){
            $cat_alias = Model_BoardCategory::getField('alias', $this->category_id);
            $city_alias = Model_BoardCity::getField('alias', $this->city_id);
            $this->_uriToMe = Route::get('board_ad')->uri(array(
                'id' => $this->id,
                'alias' => Text::transliterate($this->title, true),
                'city_alias' => $city_alias,
                'cat_alias' => $cat_alias,
            ));
        }
        return $this->_uriToMe;
    }

    /**
     * Format price by price_type
     * @return mixed|string
     */
    public function getPrice (){
        if($this->price_type == 1)
            return __('Change');
        elseif($this->price_type == 2)
            return __('For free');
        return $this->price>0 ? $this->price : __('negotiable');
    }

    /**
     * Format trade string
     * @return null|string
     */
    public function getTrade(){
        if($this->price_type==0 && $this->trade>0)
            return ' ('.__('Trade').')';
        return NULL;
    }

    /**
     * Generate content for META Title tag
     * @return string
     */
    public function getTitle(){
        $title = $this->title;
        return $title;
    }

    /**
     * Generate content for META Description tag
     * @return string
     */
    public function getDescription(){
        $description = mb_substr(strip_tags($this->description), 0, 255);
        return $description;
//        return htmlspecialchars($description);
    }

    /**
     * Добавить объявление из импорта
     * @param Array $row
     */
    public static function import_ad($row){
        /**
         * @var ORM_MPTT $new
         */
//        try{
            $new = ORM::factory('BoardAd')->values(array(
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'category_id' => $row['id_category'],
                'pcategory_id' => $row['pcategory_id'] ? $row['pcategory_id'] : 0,
                'city_id' => $row['city_id'] ? $row['city_id'] : 83,
                'pcity_id' => $row['pcity_id'] ? $row['pcity_id'] : 0,
                'type' => $row['type'] == 'p' ? 0 : 1,
                'addtime' => strtotime($row['date_add']),
                'title' => $row['title'],
                'description' => $row['text'],
                'name' => $row['autor'],
                'address' => $row['address'],
                'phone' => $row['contacts'],
                'email' => $row['email'],
                'site' => $row['url'],
                'video' => $row['video'],
                'price' => (string) $row['price'],
                'views' => $row['hits'],
                'publish' => 1,
                'photos' => 0,
//            '' => $row[''],
            ))->save();
            if($new->id != $row['id']){
                $new->id = $row['id'];
                $new->save();
            }
            unset($new);
//        }
//        catch(ORM_Validation_Exception $e){
//            echo Debug::vars($e->errors('validation/error'));
//        }
    }

    /**
     * Smart article field getter
     * @param string $name
     * @return mixed|string
     */
    public function __get($name){
        if($name == 'addTime'){
            return (date('d.m.Y', $this->addtime). ' <small class="quiet">' .date('H:i', $this->addtime) .'</small>');
        }
        elseif($name == 'descriptionHide'){
            return HTML::anchor('#', 'Hidden text', array('title' => $this->description));
        }
        return parent::__get($name);
    }

    /**
     * Increase AD view counter
     */
    public function increaseViews(){
        DB::update($this->table_name())
            ->set(array( 'views' => DB::expr('views + 1') ))
            ->where('id', '=', $this->id)
            ->execute()
        ;
    }

    public function increasePhotos(){
        DB::update($this->table_name())
            ->set(array( 'photo_count' => DB::expr('photo_count + 1') ))
            ->where('id', '=', $this->id)
            ->execute()
        ;
    }

    public function decreasePhotos(){
        if($this->photo_count > 0){
            DB::update($this->table_name())
                ->set(array( 'photo_count' => DB::expr('photo_count - 1') ))
                ->where('id', '=', $this->id)
                ->execute();
        }
    }

    /**
     * Count ads that need to be moderated
     * @return int
     */
    public static function countNotModerated(){
        $count = ORM::factory('BoardAd')->where('moderated', '=', 0)->count_all();
        return $count;
    }

    /**
     * Count ads that can be viewed
     * @return int
     */
    public static function countActiveAds(){
        $count = ORM::factory('BoardAd')->where('publish', '=', 1)->cached(Date::DAY)->count_all();
        return $count;
    }

    /**
     * Validation stop words in ad contents
     * @param Validation $validation
     * @param $field
     * @throws Kohana_Exception
     */
    public function checkStopWords(Validation $validation, $field){
        $cfg = Kohana::$config->load('stopwords')->as_array();

        $this->stopword = 0;
        $value = preg_replace('~[^a-zа-я0-9]+~ui', '', $this->{$field});
        $value = mb_strtolower($value);
        foreach($cfg['stopwords'] as $word)
            if(strpos($value, $word) !== FALSE){
                $this->stopwords++;
            }
        if($this->stopwords > 0)
            $this->stopword = 1;
    }

    /**
     * Sets AD to moderating if field has been changed
     * @param $field
     */
    public function setModerate($field){
        if($this->loaded() && $this->_original_values[$field] != $this->$field)
            $this->moderated = 0;
    }

    /**
     * Validation price field
     * @param Validation $validation
     * @param $field
     */
    public function checkPrice(Validation $validation, $field){
//        if($this->price_type == 0 && empty($this->{$field}))
//            $validation->error($field, 'not_empty');
    }

    /**
     * Validate ad TITLE for duplicates (only if USER_ID defined)
     * @param Validation $validation
     * @param $field
     */
    public function checkDuplicates(Validation $validation, $field){
        if($this->user_id && !empty($this->title)){
            $counter = ORM::factory('BoardAd')
                ->where('title','=',$this->title)
                ->and_where('user_id','=',$this->user_id);
            if($this->loaded())
                $counter->and_where('id','<>',$this->_original_values['id']);
            $count = $counter->count_all();
            if($count > 0){
                $validation->error($field, 'duplicates');
            }
        }
    }
}