<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Created by JetBrains PhpStorm.
 * User: butch
 * Date: 27.11.12
 * Time: 11:58
 * To change this template use File | Settings | File Templates.
 */

class Controller_Board extends Controller_System_Page
{
    public $board_cfg;

    public $skip_auto_render = array(
        'render_filters',
        'render_subcategories',
        'show_phone',
        'goto',
    );

    public $skip_auto_content_apply = array(
        'main',
        'tree',
    );

    public function before(){
        parent::before();

        /* Script & style */
        $this->styles[] = "media/libs/pure-release-0.5.0/grids-min.css";
        $this->styles[] = "media/libs/pure-release-0.5.0/tables-min.css";
        $this->styles[] = "media/libs/pure-release-0.5.0/menus-min.css";
        $this->styles[] = "assets/board/css/board.css";

        /* Config */
        $this->board_cfg = Kohana::$config->load('board')->as_array();

        if($this->auto_render){
            /* Bradcrumbs */
            if($this->board_cfg['board_as_module'])
                $this->breadcrumbs->add(__('Доска объявлений'), Route::get('board')->uri());

            /* Side widgets */
//            $this->template->right_column = View::factory('board/side_column');
        }
    }

    /**
     * Главная страница
     */
    public function action_main(){
        $this->breadcrumbs = Breadcrumbs::factory();
        $this->scripts[] = "assets/board/js/jquery.highlight.js";
        $this->scripts[] = "assets/board/js/jquery.tooltip.js";
        if(!$content = Cache::instance()->get("BoardMainPage")){
            $content = View::factory('board/map');
            $content->set('site_name', $this->config['project']['name']);
            $content->set('ads_count', Model_BoardAd::countActiveAds());

            $content = $content->render();
            Cache::instance()->set("BoardMainPage", $content, Date::DAY);
        }
        $this->template->content = $content;
    }

    /**
     * Категория
     */
    public function action_search(){
        $title = '';
        $ads = Model_BoardAd::boardOrmFinder();

        /* Поиск по городу */
        $childs_cities = array();
        $city_alias = $this->request->param('city_alias');
        if($city_alias && $city = Model_BoardCity::getAliases($city_alias)){
            $city = ORM::factory('BoardCity', $city);
            $title .= $city->name_in;
            $this->title = $city->name_in;
            $this->description = $city->name_in;
            $parents = $city->parents()->as_array('id');
            foreach($parents as $_parent)
                $this->breadcrumbs->add($_parent->name, $_parent->getUri());
            $this->breadcrumbs->add($city->name, $city->getUri());
            if(!$city->parent_id){
                $ads->and_where('pcity_id','=',$city->id);
                $city_counter = array();
                $_ads_city_count = DB::select('city_id', array(DB::expr('count(*)'), 'cnt'))->from( ORM::factory('BoardAd')->table_name() )->where('pcity_id', '=', $city->id)->group_by('city_id')->order_by('cnt', 'DESC')->cached(Model_BoardAd::CACHE_TIME)->execute()->as_array('city_id', 'cnt');
                $_childs = ORM::factory('BoardCity')->where('parent_id','=',$city->id)->order_by('name', 'ASC')->find_all()->as_array('id','name');
                foreach($_childs as $_city_id=>$_city){
                    if(isset($_ads_city_count[ $_city_id ]))
                        $city_counter[] = array(
                            'city_id' => $_city_id,
                            'cnt' => $_ads_city_count[ $_city_id ],
                        );
                }
//                echo Debug::vars($city_counter);
                $this->template->content->set(array(
//                    'childs_cities' => $childs_cities,
                    'city_counter'=> $city_counter,
                ));
            }
            else{
                $ads->and_where('city_id','=',$city->id);
            }
        }
        elseif($city_alias == 'all'){
            $title = $this->board_cfg['in_country'];
        }

        /* Поиск по категории */
        $category_alias = $this->request->param('cat_alias');
        $category = NULL;
        $childs_categories = array();
        if($category_alias && $category = Model_BoardCategory::getAliases($category_alias)){
            $category = ORM::factory('BoardCategory', $category);
            $parents = $category->parents()->as_array('id');
            foreach($parents as $_parent)
                $this->breadcrumbs->add($_parent->name, $_parent->getUri());
            $childs_categories = ORM::factory('BoardCategory')->where('parent_id','=',$category->id)->order_by('name', 'ASC')->find_all()->as_array();
            if(!$category->parent_id){
                $ads->and_where('pcategory_id','=',$category->id);
            }
            else{
                $ads->and_where('category_id','=',$category->id);
            }
            $title = $category->name . (!empty($title) ? ' '.$title : '' );
            $this->title = $category->getTitle() . (!empty($this->title) ? ' '.$this->title : '' );
            $this->description = $category->getDescription() . (!empty($this->description) ? ' '.$this->description : '' );
        }
        else{
            $childs_categories = ORM::factory('BoardCategory')->where('lvl','=','1')->cached(Model_BoardCategory::CATEGORIES_CACHE_TIME)->order_by('name')->find_all()->as_array();
        }

        /* Поиск по тексту */
        $_query = Arr::get($_GET, 'query');
        if(!empty($_query)){
            $ads->and_where(DB::expr('MATCH(`title`'.(Arr::get($_GET, 'wdesc') > 0 ? ',description': '').')'), 'AGAINST', DB::expr("('".Arr::get($_GET, 'query')."' IN BOOLEAN MODE)"));
        }

        /* Поиск по типу Все/Бизнес/Частное */
        if(!is_null(Arr::get($_GET, 'type'))){
            $ads->and_where('type', '=', Arr::get($_GET, 'type'));
        }

        /* Фильтр по "только фото" */
        if(Arr::get($_GET, 'wphoto') > 0){
            $ads->and_where('photo_count', '>', 0);
        }

        /* Фильтр по цене */
        if(is_array($price = Arr::get($_GET, 'price')) && ((int) Arr::get($price, 'from')>0 || (int) Arr::get($price, 'to')>0) ){
            if((int) Arr::get($price, 'from'))
                $ads->and_where('price', '>=', $price['from']);
            if((int) Arr::get($price, 'to'))
                $ads->and_where('price', '<=', $price['to']);
        }

        /* Поиск по главному фильтру (подкатегория) */
        if($category instanceof ORM && FALSE !== ($main_filter = Model_BoardFilter::loadMainFilter($category->id))){
            $main_filter['base_uri'] = URL::site(Route::get('board_subcat')->uri(array(
                'cat_alias' => Request::$current->param('cat_alias'),
                'city_alias' => Request::$current->param('city_alias'),
                'filter_alias' => '{{ALIAS}}',
            )));
            $this->template->content->set('main_filter', $main_filter);
            if(NULL !== ($filter_alias = Request::$current->param('filter_alias')) && isset($main_filter['aliases'][$filter_alias]))
                $_GET['filters'][$main_filter['id']] = $main_filter['aliases'][$filter_alias];
        }

        /* Поиск по фильтрам */
        if($category instanceof ORM && NULL !== ($filters_values = Arr::get($_GET, 'filters')) && Model_BoardFiltervalue::haveValues($filters_values)){
            $filters = Model_BoardFilter::loadFiltersByCategory($category->id);
            if(count($filters)){
                if(isset($main_filter))
                    $filters[$main_filter['id']] = $main_filter;
//                echo Debug::vars($filters);
//                echo Debug::vars($filters_values);
                foreach($filters_values as $_id=>$_val){
                    if(Model_BoardFiltervalue::haveValue($_val) && isset($filters[$_id])){
                        $ads->join(array('ad_filter_values','afv'.$_id), 'INNER');
                        $ads->on('afv'.$_id.'.filter_id','=',DB::expr($_id));
                        $ads->on('afv'.$_id.'.ad_id', '=', 'ads.id');
                        if($filters[$_id]['type'] == 'digit' && ((int) Arr::get($_val, 'from') || (int) Arr::get($_val, 'to') )){
                            $ads->where_open();
                            if((int)Arr::get($_val, 'from'))
                                $ads->and_where('afv'.$_id.'.value', '>=', $_val['from']);
                            if((int)Arr::get($_val, 'to'))
                                $ads->and_where('afv'.$_id.'.value', '<=', $_val['to']);
                            $ads->where_close();
                        }
                        elseif($filters[$_id]['type'] == 'optlist'){
                            $_bin = Model_BoardFiltervalue::optlist2mysqlBin( array_flip($_val) );
                            $ads->and_where(DB::expr('afv'.$_id.'.value & '. $_bin), '=', DB::expr($_bin));
                        }
                        elseif($filters[$_id]['type'] == 'select' && is_array($_val) && count($_val)){
                            $ads->and_where('afv'.$_id.'.value', 'IN', $_val);
                        }
                        elseif(!empty($_val) && !is_array($_val)){
                            $ads->and_where('afv'.$_id.'.value', '=', $_val);
                        }
                    }
                }
                $ads->group_by('ads.id');
            }
        }

        /* requesting Ads */
        $counter = clone($ads);
        $counter->select(DB::expr('count(*) cnt'));
        $count = $counter->cached(Model_BoardAd::CACHE_TIME)->as_assoc()->execute();
        $pagination = Pagination::factory(array(
            'total_items' => $count[0]['cnt'],
            'group' => 'board',
        ))->route_params(array(
            'controller' => Request::current()->controller(),
            'city_alias' => $city_alias,
            'cat_alias' => $category_alias,
        ));

        $ads->offset($pagination->offset)->limit($pagination->items_per_page);
//        echo $ads;
        $ads = $ads->execute();
        $ads_ids = array();
        foreach($ads as $_ad)
            $ads_ids[] = $_ad->id;
        $photos = Model_BoardAdphoto::adsPhotoList($ads_ids);

        $this->scripts[] = "assets/board/js/search.js";
        $this->scripts[] = "assets/board/js/favorite.js";
        $this->scripts[] = "assets/board/js/jquery.tipcomplete/jquery.tipcomplete.js";
        $this->styles[] = "assets/board/js/jquery.tipcomplete/jquery.tipcomplete.css";
        $this->styles[] = "assets/board/js/multiple-select/multiple-select.css";
        $this->scripts[] = "assets/board/js/multiple-select/jquery.multiple.select.js";

        $this->template->search_form = Widget::factory('BoardSearch')->render();
        $this->template->content->set(array(
            'title' => $title,
            'city' => $city,
            'category' => $category,
            'childs_categories' => $childs_categories,
            'ads' => $ads,
            'photos' => $photos,
            'cfg' => $this->board_cfg,
            'pagination' => $pagination,
        ));
    }

    /**
     * Объвяление
     */
    public function action_ad(){
        $id = $this->request->param('id');
        $alias = $this->request->param('alias');
        $print = $this->request->param('print');

        if($print){
            $this->template = View::factory('global/print');
            $this->template->content = View::factory('board/ad_print');
            $this->_setTemplateMeta();
            $this->_setTemplateAssets();
        }

        /** @var Model_BoardAd $ad */
        $ad = Model_BoardAd::boardOrmFinder()->and_where('id','=',$id)->limit(1)->execute();
        $ad = $ad[0];

        if($ad instanceof ORM && $ad->loaded() && Text::transliterate($ad->title, true) == $alias){
            $ad->increaseViews();
            $this->title = $ad->getTitle();
            $this->description = $ad->getDescription();

            /* Breadcrumbs & part parents */
            $city_parents = ORM::factory('BoardCity', $ad->city_id)->parents(true, true)->as_array('id');
            foreach($city_parents as $_parent)
                $this->breadcrumbs->add($_parent->name, $_parent->getUri());
            $city =& $city_parents[$ad->city_id];
            $region =& $city_parents[$city->parent_id];

            $category_parents = ORM::factory('BoardCategory', $ad->category->id)->parents(true, true)->as_array('id');
            foreach($category_parents as $_parent)
                $this->breadcrumbs->add($_parent->name, $_parent->getUri());

            /* Photos */
            $photos = $ad->photos->find_all();
            if(count($photos) > 1){
                $this->styles[] = "media/libs/bxSlider/jquery.bxslider.css";
                $this->scripts[] = 'media/libs/bxSlider/jquery.bxslider.min.js';
                $this->scripts[] = 'assets/board/js/board_gallery.js';
            }

            /* Other user ads */
            if($ad->user_id>0 || !empty($ad->email)){
                $user_ads = Model_BoardAd::boardOrmFinder()
                    ->and_where('id', '<>', $ad->id)
                    ->limit(10);
                if($ad->user_id > 0)
                    $user_ads->and_where('user_id', '=', $ad->user_id);
                elseif(!empty($ad->email))
                    $user_ads->and_where('email', '=', $ad->email);
                $user_ads = $user_ads->execute();
                if(count($user_ads)){
                    $user_ads_ids = array();
                    foreach($user_ads as $_ad)
                        $user_ads_ids[] = $_ad->id;

                    $this->template->content->set(array(
                        'user_ads' => $user_ads,
                        'user_ads_photos' => Model_BoardAdphoto::adsPhotoList($user_ads_ids),
                    ));
                }
            }

            /* Similar ads */
            $table = ORM::factory('BoardAd')->table_name();
            $sim_ads = DB::select($table.'.*')->from($table)
                ->select(array(DB::expr('round(MATCH (title) AGAINST ("'.$ad->title.'"))'), 'rel'))
                ->and_where('publish', '=', 1)
                ->and_where('category_id', '=', $ad->category_id)
                ->and_where('id', '<>', $ad->id)
                ->order_by('rel', 'DESC')
                ->limit(10)
                ->as_object(get_class(ORM::factory('BoardAd')))
                ->execute();
            if(count($sim_ads)){
                $sim_ads_ids = array();
                foreach($sim_ads as $_ad)
                    $sim_ads_ids[] = $_ad->id;
                $this->template->content->set(array(
                    'sim_ads' => $sim_ads,
                    'sim_ads_photos' => Model_BoardAdphoto::adsPhotoList($sim_ads_ids),
                ));
            }

            /* Filters */
            $filters = Model_BoardFilter::loadFiltersByCategory($ad->category_id);
            Model_BoardFilter::loadFilterValues($filters, NULL, $ad->id);

            $this->styles[] = "media/libs/pure-release-0.5.0/forms.css";
            $this->scripts[] = 'assets/board/js/message.js';
            $this->scripts[] = "assets/board/js/favorite.js";

            $this->template->content->set(array(
                'ad' => $ad,
                'photos' => $photos,
                'filters' => $filters,
                'region_cities_ids' => $region->getChildrenId(),
                'city' => $city,
                'city_parents' => $city_parents,
                'category_parents' => $category_parents,
                'config' => $this->config,
                'board_config' => $this->board_cfg,
                'is_job_category' => in_array($ad->category_id, Model_BoardCategory::getJobIds()),
                'is_noprice_category' => in_array($ad->category_id, Model_BoardCategory::getNopriceIds()),
            ));
        }
        else
            $this->go(Route::get('board')->uri());
    }

    /**
     * Добавление объвяления
     */
    public function action_add()
    {
        $user = $this->current_user;
        if(isset($_POST['cancel']))
            $this->go('/');

        if (HTTP_Request::POST == $this->request->method()){
            if(Arr::get($_POST, 'cancel'))
                $this->go('/board');

            $ad = ORM::factory('BoardAd')->values($_POST);
            $ad->category_id =  Arr::get($_POST, 'maincategory_id');
            if($this->logged_in)
                $ad->publish = 1;
            try{
                $validation = Validation::factory($_POST);
                if(!$this->logged_in)
                    $validation->rules('captcha', array(
                        array('not_empty'),
                        array('Captcha::checkCaptcha', array(':value', ':validation', ':field'))
                    ))->labels(array(
                        'email' => __('Your e-mail'),
                        'text' => __('Message text'),
                        'captcha' => __('Enter captcha code'),
                    ));
                $ad->check($validation);

                /**
                 * Try to find existing user OR Create New User
                 * @var $user Model_User
                 */
                if(is_null($user) && !empty($_POST['email']) && Valid::email($_POST['email'])){
                    /* Looking for existing user */
                    $user = ORM::factory('User')->where('email','=',$_POST['email'])->find();
                    if(!$user->loaded())
                        $user = NULL;
                    else{
                        $user->load_roles();
                        if($user->has_role('banned'))
                            throw new Kohana_HTTP_Exception_403(__('Your account is banned'));
                    }
                    $ad->publish = 0;
                    $ad->key = md5($ad->title . time());
                    $password = null;
                }
                if(is_null($user)){
                    /* Creating new user */
                    $user = ORM::factory('User');
                    $userdata = array();
                    $userdata['email'] = Arr::get($_POST,'email');
                    $userdata['password'] = $password = substr(md5($userdata['email'] . time()), 0,7);
                    $user->create_user($userdata, array('email', 'username', 'password'));

                    /* Creating profile */
                    $profile = ORM::factory('Profile');
                    $profile->user_id = $user->id;
                    $profile->name = Arr::get($_POST,'name');
                    $profile->phone = Arr::get($_POST,'phone');
                    $profile->address = Arr::get($_POST,'address');
                    $profile->city_id = Arr::get($_POST,'city_id');
                    $profile->save();

                    $ad->publish = 0;
                    $ad->key = md5($user->id . $user->email . time());
                }

                /* Save Ads & redirect */
                $ad->user_id = $user->id;
                $ad->save();

                /* Sending activation email */
                if(!empty($ad->key))
                    $this->_sendActivationLetter($ad, $user, $password);

                /* Save filters */
                if(NULL !== $values = Arr::get($_POST, 'filters'))
                    foreach($values as $id=>$val){
                        ORM::factory('BoardFiltervalue')->values(array(
                            'ad_id'=>$ad->id,
                            'filter_id'=>$id,
                            'value'=>$val,
                        ))->save();
                    }

                /* Save photos */
                $files = Arr::get($_FILES, 'photos', array('tmp_name' => array()));
                foreach($files['tmp_name'] as $file)
                    $ad->addPhoto($file);
                $ad->setMainPhoto();

                /* Finalize ads saving */
                Flash::success(__('Your ad successfully added'));
                if(Auth::instance()->logged_in())
                    $this->go(URL::site().Route::get('board_myads')->uri());
                else
                    $this->template->content = View::factory('board/successful');
                return true;
            }
            catch(ORM_Validation_Exception $e){
                $errors = $e->errors('', TRUE);
                /* Валидация полей объявления */
                if($e->alias() == 'user')
                    $errors = array_merge( $errors, $ad->validateData($this->request->post()) );
            }
            catch(Kohana_HTTP_Exception_403 $e){
                $errors = $e->errors('', TRUE);
                /* Валидация полей объявления */
                $errors = array_merge( $errors, $ad->validateData($this->request->post()) );
            }
        }
        else{
            $ad = ORM::factory('BoardAd');
        }

        $this->styles[] = "media/libs/pure-release-0.5.0/forms.css";
        $this->scripts[] = "assets/board/js/form.js";

        $this->styles[] = "media/libs/jquery-form-styler/jquery.formstyler.css";
        $this->scripts[] = "media/libs/jquery-form-styler/jquery.formstyler.min.js";

        /* Категории и фильтры */
        $categories_main = array(''=>"Выберите категорию");
        $categories_main += ORM::factory('BoardCategory')->where('parent_id', '=', 0)->cached(Model_BoardCategory::CATEGORIES_CACHE_TIME)->find_all()->as_array('id','name');

        $filters = '';
        $cat_child = '';
        if($ad->category_id > 0){
            $cat_child = $this->_render_subcategory_list($ad->category->parent(), $ad->category_id);
            $filters = $this->_render_filters_list($ad->category);
        }
        else{
            if(NULL !== $cat_main = Arr::get($_POST, 'cat_main')){
                $category = ORM::factory('BoardCategory', $cat_main);
                $cat_child = $this->_render_subcategory_list($category);
                $filters = $this->_render_filters_list($category);
            }
        }

        /* Регионы и города */
        $regions = array(''=>"Выберите регион");
        $regions += ORM::factory('BoardCity')->where('parent_id', '=', 0)->cached(Model_BoardCity::CITIES_CACHE_TIME)->find_all()->as_array('id','name');
        $cities = '';
        if($ad->city_id > 0){
            $cities = $this->_render_city_list($ad->city->parent(), $ad->city_id);
        }
        else{
            if(NULL !== $region = Arr::get($_POST, 'region')){
                $cities = $this->_render_city_list(ORM::factory('BoardCity', $region));
            }
        }

        $this->template->content->bind('errors', $errors);
        $this->template->content->set(array(
            'model' => $ad,
            'user' => $user,
            'price_value' => $this->board_cfg['price_value'],
            'job_ids' => Model_BoardCategory::getJobIds(),
            'noprice_ids' => Model_BoardCategory::getNopriceIds(),
            'logged' => $this->logged_in,
        ));
        $this->template->content->set(array(
            'filters' => $filters,
            'cat_child' => $cat_child,
            'categories_main' => $categories_main,
        ));
        $this->template->content->set(array(
            'regions' => $regions,
            'cities' => $cities,
        ));
    }

    /**
     * Вывод дерева регионов и городов
     */
    public function action_tree(){
        $this->breadcrumbs = Breadcrumbs::factory();
        if(!$content = Cache::instance()->get("BoardCityTreePage")){
            $content = View::factory('board/tree');
            $regions = ORM::factory('BoardCity')->where('lvl', '=', 1)->order_by('name', 'ASC')->find_all();
            $cities = array();
            foreach(ORM::factory('BoardCity')->where('lvl', '=', 2)->order_by('name', 'ASC')->find_all() as $city){
                $cities[$city->parent_id][$city->id] = $city;
            }
            $content->set(array(
                'regions' => $regions,
                'cities' => $cities,
            ));
            $content = $content->render();
            Cache::instance()->set("BoardCityTreePage", $content, Date::MONTH);
        }
        $this->template->content = $content;
    }

    /**
     * Вывод дерева разделов и категорий
     */
    public function action_categories(){
        $this->breadcrumbs = Breadcrumbs::factory();
        if(!$content = Cache::instance()->get("BoardCategoryTreePage")){
            $content = View::factory('board/categories');
            $parts = ORM::factory('BoardCategory')->where('lvl', '=', 1)->order_by('name', 'ASC')->find_all();
            $categories = array();
            foreach(ORM::factory('BoardCategory')->where('lvl', '=', 2)->order_by('name', 'ASC')->find_all() as $category){
                $categories [$category->parent_id][$category->id] = $category;
            }
            $content->set(array(
                'parts' => $parts,
                'categories' => $categories,
            ));
            $content = $content->render();
            Cache::instance()->set("BoardCategoryTreePage", $content, Date::MONTH);
        }
        $this->template->content = $content;
    }

    /**
     * Подтверждение объявления
     */
    public function action_confirm(){
        $id = Request::initial()->param('id');
        $key = Request::initial()->param('key');
        $ad = ORM::factory('BoardAd')->where('id','=', $id)->and_where('key', '=', $key)->find();
        if($ad->loaded()){
            /**
             * @var Model_User $user
             */
            $user = ORM::factory('User', $ad->user_id);
            if($user->loaded()){
                $user->load_roles();
                if(!$user->has_role('login')){
                    $role = ORM::factory('Role')->where('name', '=', 'login')->find();
                    $user->add('roles', $role);
                    Flash::success(__('Your registration successfully finished').'!');
                }
                Auth::instance()->force_login($user);
                $ad->key = '';
                $ad->publish = 1;
                try{
                    $ad->save();
                    Flash::success(__('Your ad successfully published').'!');
                }
                catch(ORM_Validation_Exception $e){
                    $errors = $e->errors('validation', TRUE);
                    Flash::error('- ' . implode("<br>- ", $errors));
                }

                $this->go(URL::site().Route::get('board_myads')->uri());
            }
        }
    }

    /**
     * Get Category Filter lists (AJAX)
     */
    public function action_ajax_filters(){
        if(!$this->request->is_ajax() && $this->request->initial())
            $this->go(Route::get('board')->uri());
        $id = $this->request->param('id');
        $category = ORM::factory('BoardCategory', Arr::get($_POST, 'selectedCategory') );

        if($category->loaded()){
            /* Rendering subcategories list */
            $this->json['filters'] = $this->_render_filters_list($category, $id);
            $this->json['id'] = $id;
        }
    }


    /**
     * Get Category children (AJAX)
     */
    public function action_ajax_subcats(){
        if(!$this->request->is_ajax() && $this->request->initial())
            $this->go(Route::get('board')->uri());
        $id = $this->request->param('id');
        $category = ORM::factory('BoardCategory', Arr::get($_POST, 'selectedCategory') );

        if($category->loaded()){
            /* Rendering subcategories list */
            $this->json['categories'] = $this->_render_subcategory_list($category);
            $this->json['filters'] = $this->_render_filters_list($category);
            $this->json['id'] = $id;
        }
    }


    /**
     * Get Region cities (AJAX)
     */
    public function action_ajax_cities(){
        if(!$this->request->is_ajax() && $this->request->initial())
            $this->go(Route::get('board')->uri());
        $id = $this->request->param('id');
        $region = ORM::factory('BoardCity', Arr::get($_POST, 'selectedRegion') );

        if($region->loaded()){
            /* Rendering subcategories list */
            $this->json['cities'] = $this->_render_city_list($region);
            $this->json['id'] = $id;
        }
    }

    /**
     * Load subfilters
     */
    public function action_sub_filter(){
        if(!$this->request->is_ajax() && $this->request->initial())
            $this->go(Route::get('board')->uri());
        $id = $this->request->param('id');
        $parent = Arr::get($_POST, 'parent');
        $value = Arr::get($_POST, 'value');
        if($id && $parent && $value){
            $parameters = array(
                'data-id' => $id,
                'data-parent' => $parent,
            );
            $options = Model_BoardFilter::loadSubFilterOptions($id, $value);
            if(!count($options))
                $parameters['disabled'] = 'disabled';
            $this->json['content'] = Form::select('filters['.$id.']', $options, $value, $parameters);
        }
        if(!empty($this->json['content']))
            $this->json['status'] = TRUE;
    }

    /**
     * Favorites page
     */
    public function action_favorites(){
        $ads = array();
        $photos = array();
        $pagination = '';
        if(isset($_COOKIE['board_favorites']) && count($_COOKIE['board_favorites'])){
            $count = count($_COOKIE['board_favorites']);
            $pagination = Pagination::factory(array(
                'total_items' => $count[0]['cnt'],
                'group' => 'board',
            ))->route_params(array(
                'controller' => Request::current()->controller(),
                'action' => Request::current()->action(),
            ));
            $ads = Model_BoardAd::boardOrmFinder()->where('id','IN', array_keys($_COOKIE['board_favorites']))->offset($pagination->offset)->limit($pagination->items_per_page)->execute();
            $ads_ids = array();
            foreach($ads as $_ad)
                $ads_ids[] = $_ad->id;
            $photos = Model_BoardAdphoto::adsPhotoList($ads_ids);
        }

        $this->scripts[] = "assets/board/js/favorite.js";
        $this->template->content->set(array(
            'ads' => $ads,
            'photos' => $photos,
            'pagination' => $pagination,
            'cfg' => $this->board_cfg,
        ));
    }

    /**
     * Handle add/remove favorites list items
     */
    public function action_favset(){
        if(!$this->request->is_ajax())
            $this->go(Route::get('board')->uri());

        if(!isset($_COOKIE['board_favorites']))
            $_COOKIE['board_favorites'] = array();

        /* Operate cookies array */
        if(isset($_POST['oper']) && $_POST['oper'] == 'add' && isset($_POST['id']) && (int) $_POST['id'] > 0 && !isset($_COOKIE['board_favorites'][$_POST['id']])){
            $_COOKIE['board_favorites'][$_POST['id']] = 1;
            foreach($_COOKIE['board_favorites'] as $_cookie_id=>$_cookie)
                setcookie('board_favorites['.$_cookie_id.']', $_cookie, null, '/');
        }
        if(isset($_POST['oper']) && $_POST['oper'] == 'del' && isset($_POST['id']) && isset($_COOKIE['board_favorites'][$_POST['id']])){
            unset($_COOKIE['board_favorites'][$_POST['id']]);
            setcookie('board_favorites['.$_POST['id'].']', NULL, NULL, '/');
        }
        $this->json['favcount'] = ' ';
        if(count($_COOKIE['board_favorites']))
            $this->json['favcount'] = count($_COOKIE['board_favorites']);
    }

    /**
     * Отображение телефона на странице объявления
     * выводит изображение
     */
    public function action_show_phone(){
        if(!$this->request->referrer())
            $this->go(Route::get('board')->uri());
        $id = $this->request->param('id');
        $ad = ORM::factory('BoardAd', $id);
        if($ad->loaded())
            echo FlyPhone::draw_canvas($ad->phone);
        echo FlyPhone::draw_canvas(__('Nothing found'));
    }

    /**
     * Redirection to article source
     * @throws HTTP_Exception_404
     */
    public function action_goto(){
        $id = $this->request->param('id');
        $ad = ORM::factory('BoardAd', $id);
        if($ad->loaded() && $ad->publish==1 && !empty($ad->site)){
            $ad->gotoSource();
        }
        else{
            throw new HTTP_Exception_404('Requested page not found');
        }
    }

    /**
     * Add user abuse to DB
     * @throws Kohana_Exception
     */
    public function action_addabuse(){
        if(!$this->request->is_ajax())
            $this->go(Route::get('board')->uri());

        $this->json['status'] = TRUE;
        $type = Request::$current->post('type');
        $ad_id = Request::$current->post('ad_id');
        if(!is_null($type) && $ad_id){
            ORM::factory('BoardAbuse')
                ->set('ad_id', $ad_id)
                ->set('type', $type)
                ->save();
            $this->json['message'] = __('Your complaint was sent to administration');
        }
        else{
            $this->json['message'] = __('Error occurred while sending complaint');
        }
    }

    /**
     * Отображение формы отправки сообщения (AJAX)
     */
    public function action_show_mailto(){
        if(!$this->request->is_ajax())
            $this->go(Route::get('board')->uri());
        $id = $this->request->param('id');
        $ad = ORM::factory('BoardAd', $id);
        if($ad->loaded()){
            $this->json['status'] = TRUE;
            $errors = array();
            if($this->request->method() == Request::POST){
                $validation = Validation::factory($_POST)
                    ->rule('email', 'not_empty')
                    ->rule('email', 'email', array(':value'))
                    ->rule('text', 'not_empty')
                    ->rule('text', 'min_length', array(':value',10))
                    ->rule('text', 'max_length', array(':value',1000))
                    ->labels(array(
                        'email' => __('Your e-mail'),
                        'text' => __('Message text'),
                        'captcha' => __('Enter captcha code'),
                    ))
                ;
                if(!$this->logged_in)
                    $validation->rules('captcha', array(
                        array('not_empty'),
                        array('Captcha::checkCaptcha', array(':value', ':validation', ':field'))
                    ));
                if($validation->check()){
                    Email::instance()
                        ->to($ad->email)
                        ->from($this->config->robot_email)
                        ->subject($this->config['project']['name'] .': '. __('Message from bulletin board'))
                        ->message(View::factory('board/mail/user_mailto_letter', array(
                                'name' => $ad->name,
                                'email'=> Arr::get($_POST, 'email'),
                                'text'=> strip_tags(Arr::get($_POST, 'text')),
                                'site_name'=> $this->config['project']['name'],
                            ))->render()
                            , true)
                        ->send();
                    Flash::success(__("Your message successfully sended"));
                    $this->json['content'] = Flash::render('global/flash');
                    return;
                }
                else
                    $errors = $validation->errors('error/validation');
            }
            $this->json['content'] = View::factory('board/user_mailto')->set(array(
                'errors' => $errors,
                'ad_id' => $ad->id,
            ))->render();
        }
    }


    /**
     * Загрузить список выпадающих фильтров
     * @param ORM_MPTT $category
     * @param integer $model_id
     * @return bool|string
     */
    protected function _render_filters_list($category, $model_id = NULL){
        $filters = Model_BoardFilter::loadFiltersByCategory($category);
        $values = Arr::get($_POST, 'filters', array());
        Model_BoardFilter::loadFilterValues($filters, $values, $model_id);

        return View::factory('board/form_filters_ajax', array('filters' => $filters))->render();
    }

    /**
     * Загрузить список дочерних категорий
     * @param ORM_MPTT $category
     * @param null $selected
     * @return bool|string
     */
    protected function _render_subcategory_list($category, $selected = NULL){
        $options = $category->children()->as_array('id', 'name');

        if(count($options)){
            $options = Arr::merge(array('' => __('Select category')), $options);
            return View::factory('board/form_subcategory_ajax', array(
                'category' => $category,
                'options' => $options,
                'selected' => $selected,
            ))->render();
        }
        return false;
    }

    /**
     * @param ORM_MPTT $region
     * @param null $selected
     * @return bool|string
     */
    protected function _render_city_list($region, $selected = NULL){
        $options = $region->children()->as_array('id', 'name');
        if(count($options)){
            $options = Arr::merge(array('' => __('Select city')), $options);
            return View::factory('board/form_cities_ajax', array(
                'region' => $region,
                'options' => $options,
                'selected' => $selected,
            ))->render();
        }
        return false;
    }

    /**
     * Rendering sub filter options list (like Models of Mark)
     * @param int $id - sub filter ID
     * @param int $parent_id - Parent filter ID
     * @param int $parent_value - value of parent filter
     * @param int|null $selected - current filter value
     * @return string
     */
    protected function _render_sub_filter($id, $parent_id, $parent_value, $selected = NULL){
        /* Load options related to selected parent option */
        $content = NULL;
        $options = ORM::factory('BoardOption')->where('filter_id','=',$id)->and_where('parent_id', '=', $parent_value)->order_by('value', 'ASC')->find_all()->as_array('id', 'value');
        if($id && $parent_id && $parent_value){
            $parameters = array(
                'data-id' => $id,
                'data-parent' => $parent_id,
            );
            if(!count($options))
                $parameters['disabled'] = 'disabled';
            $content = Form::select('filters['.$id.']', $options, $selected, $parameters);
        }
        return $content;
    }

    /**
     * Отправляет письмо со ссылкой для активации объявления (+пользователя)
     * (отправляется только, если пользователь не авторизрован или регистрация произошла после добавления объявления)
     * @param Model_BoardAd $ad
     * @param Model_User $user
     * @param null|string $password
     * @throws Kohana_Exception
     * @throws View_Exception
     */
    protected function _sendActivationLetter(Model_BoardAd $ad, Model_User $user, $password = NULL){
        Email::instance()
            ->to($user->email)
            ->from($this->config->robot_email)
            ->subject($this->config['project']['name'] .': '. __('New classified ad confirmation'))
            ->message(View::factory('board/mail/ad_confirm_letter', array(
                    'user'=>$user,
                    'password'=>$password,
                    'site_name'=> $this->config['project']['name'],
                    'server_name'=> $_SERVER['HTTP_HOST'],
                    'activation_link'=> Route::get('board_ad_confirm')->uri(array('id'=>$ad->id, 'key'=>$ad->key)),
                ))->render()
                , true)
            ->send();
    }
}