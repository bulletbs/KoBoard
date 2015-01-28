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
    );

    public function before(){
        parent::before();

        /* Script & style */
        $this->styles[] = "media/libs/pure-release-0.5.0/grids-min.css";
        $this->styles[] = "media/libs/pure-release-0.5.0/forms.css";
        $this->styles[] = "media/libs/pure-release-0.5.0/tables-min.css";
        $this->styles[] = "media/libs/pure-release-0.5.0/menus-min.css";
        $this->styles[] = "assets/board/css/board.css";
        $this->styles[] = "assets/board/js/multiple-select/multiple-select.css";

        $this->scripts[] = "assets/board/js/favorite.js";
        $this->scripts[] = "assets/board/js/search.js";
        $this->scripts[] = "assets/board/js/multiple-select/jquery.multiple.select.js";

        /* Config */
        $this->board_cfg = Kohana::$config->load('board')->as_array();

        if($this->auto_render){
            /* Bradcrumbs */
            if($this->board_cfg['board_as_module'])
                $this->breadcrumbs->add(__('Доска объявлений'), Route::get('board')->uri());

            /* Side widgets */
            $this->template->right_column = View::factory('board/side_column');
        }
    }

    /**
     * Главная страница
     */
    public function action_main(){
//        if(!$content = Cache::instance()->get("BoardMainPage")){
            $categories[0] = ORM::factory('BoardCategory')->where('lvl','=','1')->find_all();
            $result = ORM::factory('BoardCategory')->where('lvl','=','2')->find_all();
            foreach($result as $subcat)
                $categories[$subcat->parent_id][] = $subcat;
            $content = View::factory('board/main')->set(array(
                'categories'=>$categories,
                'categories_count'=>count($categories, COUNT_RECURSIVE),
            ))->render();
            Cache::instance()->set("BoardMainPage", $content, 600);
//        }
        $this->template->content = $content;
    }

    /**
     * Категория
     */
    public function action_search(){
        $title = '';
        $ads = Model_BoardAd::boardOrmFinder();
        $counter = Model_BoardAd::boardOrmCounter();

        /* Поиск по городу */
        $childs_cities = array();
        $city_alias = $this->request->param('city_alias');
        if($city_alias && $city = Model_BoardCity::getAliases($city_alias)){
            $city = ORM::factory('BoardCity', $city);
            $title .= $city->name_in;
            $parents = $city->parents()->as_array('id');
            foreach($parents as $_parent)
                $this->breadcrumbs->add($_parent->name, $_parent->getUri());
            $this->breadcrumbs->add($city->name, $city->getUri());
            $descedants = $city->descendants(TRUE)->as_array('id');
            if(!$city->parent_id){
                $ads->and_where('pcity_id','=',$city->id);
                $counter->and_where('pcity_id','=',$city->id);
            }
            else{
                $ads->and_where('city_id','=',$city->id);
                $counter->and_where('city_id','=',$city->id);
            }
            foreach($descedants as $_city)
                if($_city->lvl == $city->lvl+1)
                    $childs_cities[$_city->alias] = $_city->name;
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
            $descedants = $category->descendants(TRUE)->as_array('id');
            if(!$category->parent_id){
                $ads->and_where('pcategory_id','=',$category->id);
                $counter->and_where('pcategory_id','=',$category->id);
            }
            else{
                $ads->and_where('category_id','=',$category->id);
                $counter->and_where('category_id','=',$category->id);
            }
//            $ads->and_where('city_id','=',$city->id);
            foreach($descedants as $_cat)
                if($_cat->lvl == $category->lvl+1)
                    $childs_categories[] = $_cat;
            $title = $category->name . (!empty($title) ? ' '.$title : '' );
        }
        else{
            $childs_categories = ORM::factory('BoardCategory')->where('lvl','=','1')->find_all()->as_array('id');
        }

        /* Поиск по фильтрам */
        if($category instanceof ORM && NULL !== ($filters_values = Arr::get($_GET, 'filters')) && Model_BoardFiltervalue::haveValues($filters_values)){
            $filters = Model_BoardFilter::loadFiltersByCategory($category->id);
//            echo Debug::vars($filters);
//            echo Debug::vars($filters_values);
            $ads->join(array('ad_filter_values','afv'), 'INNER')->on('afv.ad_id', '=', 'ads.id');
            $ads->where_open();
            foreach($filters_values as $_id=>$_val){
                if(Model_BoardFiltervalue::haveValue($_val)){
                    $ads->or_where_open();
                    if($filters[$_id]['type'] == 'digit' && ((int) Arr::get($_val, 'from') || (int) Arr::get($_val, 'to') )){
                        echo Debug::vars($_val);
                        $ads->where('afv.filter_id','=',$_id);
                        $ads->where_open();
                        if((int)Arr::get($_val, 'from'))
                            $ads->and_where('afv.value', '>=', $_val['from']);
                        if((int)Arr::get($_val, 'to'))
                            $ads->and_where('afv.value', '<=', $_val['to']);
                        $ads->where_close();
                    }
                    elseif($filters[$_id]['type'] == 'optlist'){
                        $ads->where('afv.filter_id','=',$_id);
                        $_bin = Model_BoardFiltervalue::optlist2mysqlBin( array_flip($_val) );
                        $ads->and_where(DB::expr('afv.value & '. $_bin), '=', DB::expr($_bin));
                    }
                    elseif($filters[$_id]['type'] == 'select' && is_array($_val) && count($_val)){
                        $ads->where('afv.filter_id','=',$_id);
                        $ads->and_where('afv.value', 'IN', $_val);
                    }
                    elseif(!empty($_val) && !is_array($_val)){
                        $ads->where('afv.filter_id','=',$_id);
                        $ads->and_where('afv.value', '=', $_val);
                    }
                    else
                        $ads->and_where(DB::expr('1'), '=', 1);
                    $ads->or_where_close();
                }
            }
            $ads->where_close();
            $ads->group_by('ads.id');
//            echo $ads;
//            die();

            $counter->join(array('ad_filter_values','adv'), 'INNER')->on('adv.ad_id', '=', 'ads.id');
        }

        /* requesting Ads */
        $count = $counter->cached(Model_BoardAd::CACHE_TIME)->as_assoc()->execute();
        $pagination = Pagination::factory(array(
            'total_items' => $count[0]['cnt'],
            'group' => 'board',
        ))->route_params(array(
            'controller' => Request::current()->controller(),
            'city_alias' => $city_alias,
            'cat_alias' => $category_alias,
        ));
        $ads = $ads->offset($pagination->offset)->limit($pagination->items_per_page)->execute();
        $ads_ids = array();
        foreach($ads as $_ad)
            $ads_ids[] = $_ad->id;
        $photos = Model_BoardAdphoto::adsPhotoList($ads_ids);

        $this->template->content->set(array(
            'title' => $title,
            'city' => $city,
            'category' => $category,
            'childs_cities' => $childs_cities,
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
        $ad = Model_BoardAd::boardOrmFinder()->and_where('id','=',$id)->limit(1)->execute();
        $ad = $ad[0];
        if($ad->loaded() && Text::transliterate($ad->title, true) == $alias){
            $ad->views += 1;
            $ad->update();

            /* Breadcrumbs */
            $city = ORM::factory('BoardCity', $ad->city_id);
            $this->breadcrumbs->add($city->name, $city->getUri());
            $parents = ORM::factory('BoardCategory', $ad->category->id)->parents(true)->as_array('id');
            foreach($parents as $_parent)
                $this->breadcrumbs->add($_parent->name, $_parent->getUri());

            /* Photos */
            $photos = $ad->photos->find_all();
            if(count($photos) > 1){
                $this->styles[] = "media/libs/bxSlider/jquery.bxslider.css";
                $this->scripts[] = 'media/libs/bxSlider/jquery.bxslider.min.js';
                $this->scripts[] = 'assets/board/js/board_gallery.js';
            }

            $this->scripts[] = 'assets/board/js/message.js';

            $this->template->right_column = View::factory('board/side_ad_column');
            $this->template->content->set(array(
                'ad' => $ad,
                'photos' => $photos,
                'city' => $city,
                'cfg' => $this->board_cfg,
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
//            if($selected_category = Arr::get($_POST, 'subcategory'))
//                $ad->category_id = $selected_category[ max(array_keys($selected_category)) ];
//            else
            $ad->category_id =  Arr::get($_POST, 'maincategory_id');
            try{
                /**
                 * Try to find existing user OR Create New User
                 * @var $user Model_User
                 */
                if($this->logged_in)
                    $ad->publish = 1;
                if(is_null($user) && !empty($_POST['email']) && Valid::email($_POST['email'])){
                    /* Looking for existing user */
                    $user = ORM::factory('User')->where('email','=',$_POST['email'])->find();
                    if(!$user->loaded())
                        $user = NULL;
                    $ad->publish = 0;
                    $ad->key = md5($ad->title . time());
                }
                if(is_null($user)){
                    /* Creating new user */
                    $userdata = array();
                    $user = ORM::factory('User');
                    $userdata['email'] = Arr::get($_POST,'email');
                    $userdata['password'] = substr(md5($userdata['email'] . time()), 0,7);
                    $user->create_user($userdata, array('email', 'username', 'password'));
//                    $role = ORM::factory('Role')->where('name', '=', 'login')->find();
//                    $user->add('roles', $role);
                    /* Creating profile */
                    $profile = ORM::factory('Profile');
                    $profile->user_id = $user->id;
                    $profile->name = Arr::get($_POST,'name');
                    $profile->phone = Arr::get($_POST,'phone');
                    $profile->address = Arr::get($_POST,'address');
                    $profile->save();

                    $ad->publish = 0;
                    $ad->key = md5($user->id . $user->email . time());
                }

                /* Save Ads & redirect */
                $ad->user_id = $user->id;
                $ad->save();

                /* Sending activation email */
                if(!empty($ad->key))
                    $this->_sendActivationLetter($ad, $user);

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

                /* Finalize ads saving */
                Flash::success(__('Your ad successfully added'));
                if(Auth::instance()->logged_in())
                    $this->go('/profile/board');
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
        }
        else{
            $ad = ORM::factory('BoardAd');
            if(!$this->logged_in)
                $user = ORM::factory('User');
        }

        $this->scripts[] = "assets/board/js/form.js";
        $categories = array(''=>"Выберите категорию");
        $categories += ORM::factory('BoardCategory')->getTwoLevelArray();
        $this->template->content->set('categories', $categories);
        $this->template->content->set('cities', ORM::factory('BoardCity')->getTwoLevelArray());

        /* Если была выбрана категория - загружаем дерево категорий и фильтры */
        $subcategories = '';
        $filters = '';
        if($ad->category_id > 0){
            $filters = $this->_render_filters_list($ad->category, $ad->id);
        }

        $this->template->content->bind('user', $user);
        $this->template->content->bind('model', $ad);
        $this->template->content->bind('price_value', $this->board_cfg['price_value']);
        $this->template->content->bind('errors', $errors);
        $this->template->content->bind('subcategories', $subcategories);
        $this->template->content->bind('filters', $filters);
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
                if(!$user->has_role('login')){
                    $role = ORM::factory('Role')->where('name', '=', 'login')->find();
                    $user->add('roles', $role);
                }
                Auth::instance()->force_login($user);
                $ad->key = '';
                $ad->save();

                Flash::success(__('Your registration successfully finished').'!');
                Flash::success(__('Your ad successfully published').'!');
                $this->go('/profile/board');
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
            $this->json['holder'] = '#subcategories_'. ($category->lvl+1);
            $this->json['categories'] = $this->_render_subcategory_list($category);
            $this->json['filters'] = $this->_render_filters_list($category, $id);
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
     * @param ORM_MPTT $category
     * @param null $selected
     * @param null $inner_html
     * @return bool|string
     */
    protected function _render_subcategory_list($category, $selected = NULL, $inner_html = NULL){
        $options = $category->children()->as_array('id', 'name');
        if(count($options)){
            $options = Arr::merge(array('' => __('Select category')), $options);
            return View::factory('board/form_subcategory_ajax', array(
                'category' => $category,
                'options' => $options,
                'selected' => $selected,
                'inner_html' => $inner_html,
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
     * Favorites page
     */
    public function action_favorites(){
        $ads = array();
        $photos = array();
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
     * Отображение формы отправки сообщения (AJAX)
     */
    public function action_show_mailto(){
//        if(!$this->request->is_ajax())
//            $this->go(Route::get('board')->uri());
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
                        ->message(View::factory('board/user_mailto_letter', array(
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
     * Отправляет письмо со ссылкой для активации объявления (+пользователя)
     * (отправляется только, если пользователь не авторизрован или регистрация произошла после добавления объявления)
     * @param $ad
     * @param $user
     */
    protected function _sendActivationLetter(Model_BoardAd $ad, Model_User $user){
        Email::instance()
            ->to($user->email)
            ->from($this->config->robot_email)
            ->subject($this->config['project']['name'] .': '. __('New classified ad confirmation'))
            ->message(View::factory('board/ad_confirm_letter', array(
                        'user'=>$user,
                        'site_name'=> $this->config['project']['name'],
                        'server_name'=> $_SERVER['HTTP_HOST'],
                        'activation_link'=> 'board/confirm/'.$ad->id.'-'.$ad->key,
                ))->render()
                , true)
            ->send();
    }
}