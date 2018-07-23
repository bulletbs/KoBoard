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
        'pagemoved',
	    'adold',
    );

    public $skip_auto_content_apply = array(
        'main',
        'tree',
        'tags',
    );

    public function before(){
        parent::before();

        /* Script & style */
        $this->styles[] = "assets/board/css/board.css?ver=1.0";
        $this->styles[] = 'media/libs/pure-release-0.6.0/grids-min.css';

        /* Config */
        $this->board_cfg = Kohana::$config->load('board')->as_array();

        if($this->auto_render){
            /* Bradcrumbs */
            if($this->board_cfg['board_as_module'])
                $this->breadcrumbs->add(__('Доска объявлений'), Route::get('board')->uri());
        }
    }

    /**
     * Главная страница
     */
    public function action_main(){
        $this->breadcrumbs = Breadcrumbs::factory();
        $this->scripts[] = "assets/board/js/jquery.highlight.js";
        $this->scripts[] = "assets/board/js/jquery.tooltip.js";

        $this->add_meta_content(array('property'=>'og:title', 'content'=>KoMS::config()->view['title']));
        $this->add_meta_content(array('property'=>'og:type', 'content'=>'website'));
        $this->add_meta_content(array('property'=>'og:url', 'content'=>URL::base(Request::initial())));
        $this->add_meta_content(array('property'=>'og:site_name', 'content'=>KoMS::config()->project['name']));
        $this->add_meta_content(array('property'=>'og:description', 'content'=>KoMS::config()->view['description']));
        $this->add_meta_content(array('property'=>'og:image', 'content'=> URL::base(Request::initial()) . "media/css/images/logo.png"));

        if(!$content = Cache::instance()->get( $this->getCacheName("BoardMainPage") )){
            if(BoardConfig::instance()->show_ads_on_main){
                $last_ads = Model_BoardAd::getLastAds(20, array(
                	'timefrom' => strtotime("-1 day"),
                ));
                $ads_ids = array();
                foreach ($last_ads as $_ad)
                    $ads_ids[] = $_ad->id;
                $last_ads_photos = Model_BoardAdphoto::adsPhotoList($ads_ids);
                $content = $this->getContentTemplate('board/main_ads');
                $content->set(array(
                    'last_ads' => $last_ads,
                    'last_ads_photos' => $last_ads_photos,
                ));
            }
            else
                $content = $this->getContentTemplate('board/main_map');
            $content->set('site_name', $this->config['project']['name']);
            $content->set('ads_count', Model_BoardAd::countActiveAds());

            $content = $content->render();
            Cache::instance()->set($this->getCacheName("BoardMainPage"), $content, Date::MINUTE*10);
        }
        $this->template->content = $content;
    }

    /**
     * Категория
     */
    public function action_search(){
    	$search = BoardSearch::instance();

        /*****************
         * scripts / styles / widgets
         */
        $this->scripts[] = "assets/board/js/search.js";
        $this->scripts[] = "assets/board/js/favorite.js";
        $this->scripts[] = "assets/board/js/jquery.tipcomplete/jquery.tipcomplete.js";
        $this->styles[] = "assets/board/js/jquery.tipcomplete/jquery.tipcomplete.css";
        $this->styles[] = "assets/board/js/multiple-select/multiple-select.css";
        $this->scripts[] = "assets/board/js/multiple-select/jquery.multiple.select.js";
        $this->scripts[] = "media/libs/jquery-ui-1.12.1.custom/jquery-ui.min.js";
        $this->styles[] = "media/libs/jquery-ui-1.12.1.custom/jquery-ui.min.css";
        $this->scripts[] = "media/libs/jquery.lazyload/jquery.lazyload.min.js";
        $this->styles[] = "media/libs/toastr/build/toastr.css";
        $this->scripts[] = "media/libs/toastr/build/toastr.min.js";

        $this->breadcrumbs->setOption('addon_class', 'bread_crumbs_search');
        $this->template->search_form = Widget::factory('BoardSearch')->render();

	    /* Trying to load cache (выключено в моб.версии) */
	    if(!$this->is_mobile && BoardConfig::instance()->board_cache && FALSE !== ($cache_content = $search->read_search_cache())){
		    $search->cached();
		    $this->template->content = $cache_content['content'];
		    if(isset($cache_content['page_data']))
		        $this->_addPageData($cache_content['page_data']);
		    return true;
	    }
	    /* Generate page */
	    else{
		    $search->init();
		    try{
			    $search->search();
		    }
		    catch(HTTP_Exception_404 $e){
			    $this->redirect(Request::current()->route()->uri(array(
				    'controller' => Request::current()->controller(),
				    'city_alias' => $search->city_alias,
				    'cat_alias' => $search->category_alias,
			    )));
		    }
			/* Meta tags */
			$page_data = $search->generateFullPageData();
			$this->_addPageData($page_data);
		    $this->template->content->set( $search->template );
	    }

	    /* Сохранить страницу в кеш (выключено в моб.версии) */
	    if(!$this->is_mobile && BoardConfig::instance()->board_cache){
		    $search->write_search_cache(array(
			    'content' => $this->template->content->render(),
			    'page_data' => $page_data,
		    ));
	    }
    }

    /**
     * Search ads by tag
     * @throws HTTP_Exception_404
     */
    public function action_tags(){
        $cat_alias = Request::current()->param('cat_alias');
        $tagid = Request::current()->param('tagid');
        $_tag = ORM::factory('BoardSearch', $tagid);
        if(!$_tag->loaded())
            throw new HTTP_Exception_404();
        $_GET['query'] = $_tag->query;

        $ads_query = Model_BoardAd::boardOrmFinder();
        /* Category search */
        if($cat_alias == 'all'){
//            $ads_query->where('pcategory_id', '=', 0);
        }
        elseif($cat_alias){
            $category = ORM::factory('BoardCategory', Model_BoardCategory::getCategoryIdByAlias($cat_alias));
            $parents = $category->parents()->as_array('id');
            foreach($parents as $_parent)
                $this->breadcrumbs->add($_parent->name, $_parent->getUri(BoardConfig::instance()->country_alias));
            if(BoardConfig::instance()->breadcrumbs_category_title)
                $this->breadcrumbs->add($category->name, $category->getUri(BoardConfig::instance()->country_alias));
            if(!$category->parent_id){
                $ads_query->where('pcategory_id', '=', $category->id);
            }
            else{
                $ads_query->where('category_id', '=', $category->id);
            }
            $this->description = $category->getDescription() . (!empty($this->description) ? ' '.$this->description : '' );
        }
	    /* Query search & count */
	    $ads_query->and_where(DB::expr('MATCH(`title`)'), 'AGAINST', DB::expr("('".$_tag->query."' IN BOOLEAN MODE)"));
	    $cnt_query = DB::query(Database::SELECT, str_replace('`ads`.*', 'COUNT(*) AS cnt', (string) $ads_query))->cached(Model_BoardAd::CACHE_TIME)->as_assoc();
        $count = $cnt_query->execute();
        $count = min(1000, $count[0]['cnt']);

        /* Init pagination */
        $route_params = array(
            'controller' => Request::current()->controller(),
            'cat_alias' => $cat_alias,
            'tagid' => $tagid,
        );
        $pagination = Pagination::factory(array(
            'total_items' => $count,
            'group' => 'board',
        ))->route_params($route_params);

        /* Searching ads by tag */
        $ads = array();
        $photos = array();
        $ads_query->offset($pagination->offset)->limit($pagination->items_per_page);
        $spinx_ads = $ads_query->execute();
        if($spinx_ads && count($spinx_ads)){
            foreach($spinx_ads as $_ad)
                $ads[] = $_ad->id;
            $ads = ORM::factory('BoardAd')->where('id', 'IN', $ads)->and_where('publish', '=', 1)->order_by(DB::expr("FIELD(id, ".implode(',', $ads).")"))->find_all()->as_array('id');
            $photos = Model_BoardAdphoto::adsPhotoList(array_keys($ads));
        }

        /* Meta tags init */
	    $templates = BoardConfig::instance()->getValuesArray(array(
		    'h1' => 'tags_h1',
		    'title' => 'tags_title',
		    'description' => 'tags_description',
		    'keywords' => 'ad_keywords',
	    ));

	    $meta_generator = MetaGenerator::instance()->setValues(array('tag' => Text::mb_ucfirst($_tag->query)));

        $title = $meta_generator->setTemplate($templates['h1'])->generate();
        $this->title = $meta_generator->setTemplate($templates['title'])->generate();
        $this->description = $meta_generator->setTemplate($templates['description'])->generate();

        $this->scripts[] = "assets/board/js/search.js";
        $this->scripts[] = "assets/board/js/favorite.js";
        $this->scripts[] = "assets/board/js/jquery.tipcomplete/jquery.tipcomplete.js";
        $this->styles[] = "assets/board/js/jquery.tipcomplete/jquery.tipcomplete.css";
        $this->styles[] = "assets/board/js/multiple-select/multiple-select.css";
        $this->scripts[] = "assets/board/js/multiple-select/jquery.multiple.select.js";
        $this->scripts[] = "media/libs/jquery-ui-1.12.1.custom/jquery-ui.min.js";
        $this->styles[] = "media/libs/jquery-ui-1.12.1.custom/jquery-ui.min.css";
        $this->scripts[] = "media/libs/jquery.lazyload/jquery.lazyload.min.js";
        $this->breadcrumbs->setOption('addon_class', 'bread_crumbs_search');

        $this->template->search_form = Widget::factory('BoardSearch')->render();
        $this->template->content = $this->getContentTemplate('board/search')->set(array(
            'title' => $title,
            'city' => BoardConfig::instance()->country_alias,
            'ads' => $ads,
            'photos' => $photos,
            'board_config' => $this->board_cfg,
            'pagination' => $pagination,
            'search_by_user' => TRUE,
        ));
    }

	/**
	 * Объвяление
	 */
	public function action_adold(){
		$id = $this->request->param('id');
		$ad = Model_BoardAd::boardOrmFinder()->and_where('id','=',$id)->limit(1)->execute();
		$ad = $ad[0];
		if($ad instanceof ORM && $ad->loaded() && (empty($alias) || Text::transliterate($ad->title, true) == $alias)){
            $this->redirect(URL::base() . $ad->getUri(), 301);
		}
		elseif(BoardConfig::instance()->redirect_noad){
			$alias = Request::current()->param('alias');
			$category_alias = Request::current()->param('cat_alias');
			$city_alias = Request::current()->param('city_alias');
			if(!is_null($alias)){
				$category = ORM::factory('BoardCategory')->where('alias','=',$category_alias)->find();
				if($category->loaded()){
					$this->redirect(URL::base() . $category->getUri($city_alias), 301);
					die();
				}
			}
		}
		throw new HTTP_Exception_404();
	}

    /**
     * Объвяление
     */
    public function  action_ad(){
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

        if($ad instanceof ORM && $ad->loaded() && (empty($alias) || Text::transliterate($ad->title, true) == $alias)){
            if(BoardConfig::instance()->ad_last_modify){
                if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $ad->addtime){
                    header('HTTP/1.1 304 Not Modified');
                    die;
                }
                $this->add_page_header('Last-Modified: '.gmdate('D, d M Y H:i:s', $ad->addtime).' GMT');
            }

            /* Breadcrumbs & part parents */
            $city_parents = ORM::factory('BoardCity', $ad->city_id)->parents(true, true)->as_array('id');
            $city = $city_parents[$ad->city_id];
            $region = isset($city_parents[$city->parent_id]) ? $city_parents[$city->parent_id] : $city;
            if(BoardConfig::instance()->breadcrumbs_ad_region_all){
                foreach($city_parents as $_parent)
                    $this->breadcrumbs->add($_parent->name, $_parent->getUri());
            }
            else{
                $this->breadcrumbs = Breadcrumbs::factory()->add(BoardConfig::instance()->breadcrumbs_prefix.$city->name_of, $city->getUri());
            }

            $category_parents = ORM::factory('BoardCategory', $ad->category->id)->parents(true, true)->as_array('id');
            foreach($category_parents as $_parent)
                $this->breadcrumbs->add($_parent->name, $_parent->getUri($city->alias));
            if(BoardConfig::instance()->breadcrumbs_ad_title)
                $this->breadcrumbs->add($ad->getTitle(), FALSE);

            /* Check city and category alias */
            if(BoardConfig::instance()->redirect_ad_wrong_city && Request::current()->param('city_alias') != $city->alias)
                $this->redirect($ad->getUri(), 301);
            if(BoardConfig::instance()->redirect_ad_wrong_cat && Request::current()->param('cat_alias') != $ad->category->alias)
                $this->redirect($ad->getUri(), 301);


            /* Photos */
            $photos = $ad->photos->order_by('id', 'ASC')->find_all();
            if(count($photos) > 1){
                $this->styles[] = "media/libs/bxSlider/jquery.bxslider.css";
                $this->scripts[] = 'media/libs/bxSlider/jquery.bxslider.min.js';
                $this->scripts[] = 'assets/board/js/board_gallery.js';
            }

            /* Company */
            if($ad->company_id > 0){
                $company = ORM::factory('CatalogCompany', $ad->company_id);
                if($company->loaded())
                    $this->template->content->set("company", $company);
            }

            /* Other user ads */
            if(BoardConfig::instance()->user_ads_show && $ad->user_id>0){
                $user_ads = Model_BoardAd::boardOrmFinder()
                    ->and_where('id', '<>', $ad->id)
                    ->limit($this->board_cfg['user_ads_limit']);
                if($ad->user_id > 0)
                    $user_ads->and_where('user_id', '=', $ad->user_id);
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
            if(BoardConfig::instance()->similars_ads_show){
	            $sim_ads = array();

//	            $sim_ads = Model_BoardAd::similarQueryUni($ad)->execute()->as_array('id');

	            $sim_ads_res = Model_BoardAd::similarQuery($ad->getTitle(), array(
	            	'city_id' => $ad->city_id,
	            	'category_id' => $ad->category_id,
	            	'user_id' => $ad->user_id,
	            ))->execute();
	            foreach($sim_ads_res as $_ad)
		            $sim_ads[(string) $_ad->id] = $_ad;
	            if(count($sim_ads) < BoardConfig::instance()->similars_ads_limit){
		            $sim_ads_res = Model_BoardAd::similarQuery($ad->getTitle(), array(
			            'pcity_id' => $ad->pcity_id,
			            'pcategory_id' => $ad->pcategory_id,
			            'user_id' => $ad->user_id,
			            'exclude_ids' => array_keys($sim_ads),
		            ))->execute();
		            foreach($sim_ads_res as $_ad)
			            $sim_ads[(string) $_ad->id] = $_ad;
	            }
	            if(count($sim_ads) < BoardConfig::instance()->similars_ads_limit){
		            $sim_ads_res = Model_BoardAd::similarQuery($ad->getTitle(), array(
			            'category_id' => $ad->category_id,
			            'user_id' => $ad->user_id,
			            'exclude_ids' => array_keys($sim_ads),
		            ))->execute();
		            foreach($sim_ads_res as $_ad)
			            $sim_ads[(string) $_ad->id] = $_ad;
	            }

                if(count($sim_ads)){
	            	$sim_ads = array_slice($sim_ads,0, BoardConfig::instance()->similars_ads_limit, TRUE);
                    $this->template->content->set(array(
                        'sim_ads' => $sim_ads,
                        'sim_ads_photos' => Model_BoardAdphoto::adsPhotoList(array_keys($sim_ads)),
                    ));
                }
            }

            /* Filters */
            $filters = Model_BoardFilter::loadFiltersByCategory($ad->category_id);
            Model_BoardFilter::loadFilterValues($filters, NULL, $ad->id);

            $ad->increaseViews();

            $ad_meta_params = array(
                'project' => KoMS::config()->project['name'],
                'ad_id' => $ad->id,
                'ad_title' => $ad->getTitle(),
                'ad_price' => html_entity_decode( $ad->getPrice( BoardConfig::instance()->priceTemplate($ad->price_unit) )),
                'ad_descr' => $ad->getMetaDescription(),
                'pcategory' => $category_parents[ $category_parents[$ad->category_id]->parent_id ]->name,
                'category' => $category_parents[$ad->category_id]->name,
                'city' => $city->name,
                'city_of' => $city->name_of,
                'city_in' => $city->name_in,
                'region' => $region->name,
            );

            $templates = BoardConfig::instance()->getValuesArray(array(
            	'h1' => 'ad_h1',
            	'title' => 'ad_title',
            	'description' => 'ad_description',
            	'keywords' => 'ad_keywords',
            ));

	        $meta_generator = MetaGenerator::instance()->setValues($ad_meta_params);

            $title = $meta_generator->setTemplate($templates['h1'])->generate();
            $this->title = $meta_generator->setTemplate($templates['title'])->generate();
            $this->description = $meta_generator->setTemplate($templates['description'])->generate();
            $this->keywords = $meta_generator->setTemplate($templates['keywords'])->generate();

	        // generate tags

            $this->add_meta_content(array('property'=>'og:title', 'content'=>htmlspecialchars($ad->getTitle())));
            $this->add_meta_content(array('property'=>'og:type', 'content'=>'website'));
            $this->add_meta_content(array('property'=>'og:url', 'content'=>URL::base(Request::initial()).$ad->getUri()));
            $this->add_meta_content(array('property'=>'og:site_name', 'content'=>$this->config['project']['host']));
            $this->add_meta_content(array('property'=>'og:description', 'content'=>$ad->getMetaDescription()));
            $this->add_meta_content(array('property'=>'og:image', 'content'=> count($photos) ? $photos[0]->getPhotoUri() : URL::base(Request::initial())."media/css/images/logo.png"));
            if(!$this->is_mobile)
                $this->add_meta_content(array('tag'=>'link', 'rel'=>'canonical', 'href'=>URL::base(KoMS::protocol()).$ad->getUri()));

            if($this->is_mobile){
                $this->mobile_scripts[] = 'assets/board/js/message.js';
                $this->mobile_scripts[] = "assets/board/js/favorite.js";

                $this->mobile_scripts[] = 'media/libs/uikit-2.24.3/js/components/lightbox.min.js';
                $this->mobile_styles[] = 'media/libs/uikit-2.24.3/css/components/slidenav.almost-flat.min.css';
            }
            else{
                $this->styles[] = "media/libs/pure-release-0.6.0/forms.css";
                $this->scripts[] = 'assets/board/js/message.js';
                $this->scripts[] = "assets/board/js/search.js";
                $this->scripts[] = "assets/board/js/favorite.js";
                $this->scripts[] = "assets/board/js/jquery.tipcomplete/jquery.tipcomplete.js";
                $this->styles[] = "assets/board/js/jquery.tipcomplete/jquery.tipcomplete.css";
                $this->styles[] = "assets/board/js/multiple-select/multiple-select.css";
                $this->scripts[] = "assets/board/js/multiple-select/jquery.multiple.select.js";
                $this->scripts[] = "media/libs/jquery-ui-1.12.1.custom/jquery-ui.min.js";
                $this->styles[] = "media/libs/jquery-ui-1.12.1.custom/jquery-ui.min.css";
                $this->scripts[] = "media/libs/jquery.lazyload/jquery.lazyload.min.js";
                $this->styles[] = "media/libs/toastr/build/toastr.css";
                $this->scripts[] = "media/libs/toastr/build/toastr.min.js";
            }

            /* Bottom breadcrumbs */
            $this->breadcrumbs->setOption('addon_class', 'bread_crumbs_ad');
            $breadcrumbs = clone $this->breadcrumbs;
            $breadcrumbs->setOption('addon_class', 'bread_crumbs_message');

            /* Other elements */
            if(BoardConfig::instance()->title_in_search)
                $_GET['query'] = $ad->getShortTitle();
            $region_counts = Model_BoardCity::regionCounter($region->id, $ad->category_id, 100);

            if(BoardConfig::instance()->ad_search_form)
                $this->template->search_form = Widget::factory('BoardSearch')->render();
            $this->template->content->set(array(
                'title' => $title,
                'ad' => $ad,
//                'next' => $ad->getNextItem(),
//                'prev' => $ad->getPrevItem(),
                'photos' => $photos,
                'filters' => $filters,
                'price_template' => BoardConfig::instance()->priceTemplate($ad->price_unit),
                'region_cities_counts' => $region_counts['all'],
                'city' => $city,
                'region' => $region,
                'is_job_category' => in_array($ad->category_id, Model_BoardCategory::getJobIds()),
                'is_noprice_category' => in_array($ad->category_id, Model_BoardCategory::getNopriceIds()),
                'breadcrumbs' => $breadcrumbs,
            ));
        }
        elseif($ad instanceof ORM && $ad->loaded() && Text::transliterate($ad->title, true) != $alias){
            $this->redirect(URL::base() . $ad->getUri(), 301);
//	        throw new HTTP_Exception_404();
        }
        else{
        	if(BoardConfig::instance()->redirect_noad){
		        $category_alias = Request::current()->param('cat_alias');
		        $city_alias = Request::current()->param('city_alias');
		        if(!is_null($alias)){
			        $category = ORM::factory('BoardCategory')->where('alias','=',$category_alias)->find();
			        if($category->loaded()){
				        $this->redirect(URL::base() . $category->getUri($city_alias), 301);
				        die();
			        }
		        }
	        }
	        throw new HTTP_Exception_404();
        }
    }

    /**
     * Добавление объвяления
     */
    public function action_add()
    {
        /* Добавление временно закрыто */
        if($this->board_cfg['addnew_suspend']){
            $this->template->content = View::factory('board/add_suspended');
            return;
        }

        /* Добавление для зарегистрированых проводить через личный кабинет */
        if(Auth::instance()->logged_in('login')){
            $this->redirect( Route::get('board_myads')->uri(array('action'=>'edit')) );
        }

        $user = $this->current_user;
        if(isset($_POST['cancel']))
            $this->go('/');

        if (HTTP_Request::POST == $this->request->method()){
            if(Arr::get($_POST, 'cancel'))
                $this->go('/board');

            $ad = ORM::factory('BoardAd')->values($_POST);
            $ad->category_id =  Arr::get($_POST, 'maincategory_id');
            if($this->logged_in){
                $ad->publish = 1;
                $ad->email = $user->email;
            }
            try{
                $validation = Validation::factory($_POST)->rules('termagree', array(
                    array('Model_BoardAd::checkAgree', array(':value', ':validation', ':field'))
                ));
                if(!$this->logged_in || Model_BoardAd::checkFrequentlyAdded()){
                    $validation
                        ->rules('g-recaptcha-response', array(
                            array('Captcha::check', array(':value', ':validation', ':field'))
                        ))
                        ->rules('email', array(
                            array(array('Model_User', 'checkBannedDomain'), array(':validation', ':value')),
                        ))
                        ->labels(array(
                            'email' => __('E-mail'),
                            'text' => __('Message text'),
                            'captcha' => __('Enter captcha code'),
                    ));
                }
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
                        if($user->has_role('company'))
                            throw new Kohana_HTTP_Exception_403(__('Companies can add ads only from private cabinet'));
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

                /* AD Job type value */
                $ad->type = in_array($ad->category_id, Model_BoardCategory::getJobIds()) ? Arr::get($_POST, 'type', 0) : 0;

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

                $files = Arr::get($_FILES, 'photos', array('tmp_name' => array()));
                /* Check for big photos */
                if(in_array(UPLOAD_ERR_INI_SIZE, $files['error'])){
                    foreach($files['error'] as $_file_id=>$_error)
                        if($_error == UPLOAD_ERR_INI_SIZE){
                            Flash::warning(__('File :file too big to be uploaded (max=:max bytes)', array(':file'=>$files['name'][$_file_id], ':max'=>ini_get('upload_max_filesize'))));
                        }
                }
                /* Save photos */
                foreach($files['tmp_name'] as $file)
                    $ad->addPhoto($file);
                $ad->setMainPhoto();

                /* Finalize ads saving */
                if(Auth::instance()->logged_in()){
                    Flash::info($this->getContentTemplate('board/published')->set('ad', $ad)->render());
                    $this->go(URL::site().Route::get('board_myads')->uri());
                }
                else{
                    Flash::success(__('Your ad successfully added'));
                    $this->template->content = View::factory('board/successful');
                }
                return true;
            }
            catch(ORM_Validation_Exception $e){
                $errors = $e->errors('', TRUE);
                /* Валидация полей объявления */
                if($e->alias() == 'user'){
                    $errors = array_merge(
                        $errors,
                        $ad->validateData(Arr::merge(
                            $this->request->post(),
                            array('category_id'=>$this->request->post('maincategory_id'))
                        ))
                    );
                }
            }
            catch(Kohana_HTTP_Exception_403 $e){
                $errors[] = $e->getMessage();
            }
        }
        else{
            $ad = ORM::factory('BoardAd');
        }

        /* Категории и фильтры */
        $categories_main = array(''=>"Выберите категорию");
        $categories_main += ORM::factory('BoardCategory')->where('parent_id', '=', 0)->cached(Model_BoardCategory::CATEGORIES_CACHE_TIME)->order_by('name','ASC')->find_all()->as_array('id','name');

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
        $regions += ORM::factory('BoardCity')->where('parent_id', '=', 0)->order_by('name')->cached(Model_BoardCity::CITIES_CACHE_TIME)->find_all()->as_array('id','name');
        $cities = '';
        $city_id = Arr::get($_POST, 'city_id');
        if($ad->city_id > 0){
            $city_id = Arr::get($_POST, 'city_id', $ad->city->id);
            $region = Arr::get($_POST, 'region', $ad->city->parent_id);;
            $cities = $this->_render_city_list($ad->city->parent(), $city_id);
        }
        elseif($this->logged_in){
            $city = ORM::factory('BoardCity', $user->profile->city_id);
            $city_id = Arr::get($_POST, 'city_id', $user->profile->city_id);
            $region = $city->parent_id;
            $cities = $this->_render_city_list($city->parent(), $this->current_user->profile->city_id);
        }
        else{
            $region = Arr::get($_POST, 'region');
            if(!is_null($region))
                $cities = $this->_render_city_list(ORM::factory('BoardCity', $region), Arr::get($_POST, 'city_id'));
        }

        /* META tags */
	    $this->title = MetaGenerator::instance( BoardConfig::instance()->add_title )
	                                ->setValues(array('project'=>KoMS::config()->project['name']))
	                                ->generate();

        /* Templates & styles*/
        if($this->is_mobile){
            $this->mobile_scripts[] = "media/libs/jquery-input-limit/jquery.limit-1.2.source.js";

            $this->mobile_scripts[] = "media/libs/poshytip-1.2/jquery.poshytip.min.js";
            $this->mobile_styles[] = "media/libs/poshytip-1.2/tip-yellowsimple/tip-yellowsimple.css";
            $this->mobile_scripts[] = "assets/board/js/form.js";
        }
        else{
            $this->styles[] = "media/libs/pure-release-0.6.0/forms.css";
            $this->scripts[] = "media/libs/poshytip-1.2/jquery.poshytip.min.js";
            $this->styles[] = "media/libs/poshytip-1.2/tip-yellowsimple/tip-yellowsimple.css";
            $this->scripts[] = "assets/board/js/form.js?ver=1.1";

            $this->styles[] = "media/libs/jquery-form-styler/jquery.formstyler.css";
            $this->scripts[] = "media/libs/jquery-form-styler/jquery.formstyler.min.js";
            $this->scripts[] = "media/libs/jquery-input-limit/jquery.limit-1.2.source.js";
            $this->scripts[] = "media/libs/jquery.lazyload/jquery.lazyload.min.js";
        }

        $this->template->content->bind('errors', $errors);
        $this->template->content->set(array(
            'model' => $ad,
            'user' => $user,
            'units_options' => BoardConfig::instance()->unitsOptions(),
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
            'region' => $region,
            'cities' => $cities,
            'city_id' => $city_id,
        ));
    }

    /**
     * Вывод дерева регионов и городов
     */
    public function action_tree(){
        $this->breadcrumbs = Breadcrumbs::factory();
	    $this->title = MetaGenerator::instance( BoardConfig::instance()->region_map_title )
	                                ->setValues(array('project'=>KoMS::config()->project['name']))
	                                ->generate();
        if(!$content = Cache::instance()->get( $this->getCacheName("BoardCityTreePage"))){
            $content = $this->getContentTemplate('board/tree');
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
            Cache::instance()->set($this->getCacheName("BoardCityTreePage"), $content, Date::MONTH);
        }
        $this->template->content = $content;
    }

    /**
     * Вывод дерева разделов и категорий
     */
    public function action_categories(){
        $this->breadcrumbs = Breadcrumbs::factory();
	    $this->title = MetaGenerator::instance( BoardConfig::instance()->category_map_title )
            ->setValues(array('project'=>KoMS::config()->project['name']))
            ->generate();

        if(!$content = Cache::instance()->get($this->getCacheName("BoardCategoryTreePage"))){
            $content = $this->getContentTemplate('board/categories');
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
            Cache::instance()->set($this->getCacheName("BoardCategoryTreePage"), $content, Date::MONTH);
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
//                    Flash::success(__('Your ad successfully published').'!');
                    Flash::info($this->getContentTemplate('board/published')->set('ad', $ad)->render());
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
            $this->json['content'] = $this->_render_sub_filter($id, $parent, $value);
//            $parameters = array(
//                'data-id' => $id,
//                'data-parent' => $parent,
//            );
//            $options = Model_BoardFilter::loadSubFilterOptions($id, $value);
//            if(!count($options))
//                $parameters['disabled'] = 'disabled';
//            $this->json['content'] = Form::select('filters['.$id.']', $options, $value, $parameters);
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
        if($this->is_mobile)
            $this->mobile_scripts[] = "assets/board/js/favorite.js";
        else
            $this->scripts[] = "assets/board/js/favorite.js";
        $this->styles[] = "media/libs/toastr/build/toastr.css";
        $this->scripts[] = "media/libs/toastr/build/toastr.min.js";
        $this->template->content->set(array(
            'ads' => $ads,
            'photos' => $photos,
            'pagination' => $pagination,
            'board_config' => $this->board_cfg,
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
                setcookie('board_favorites['.$_cookie_id.']', $_cookie, null, '/', '.'.$this->config['project']['host']);
        }
        if(isset($_POST['oper']) && $_POST['oper'] == 'del' && isset($_POST['id']) && isset($_COOKIE['board_favorites'][$_POST['id']])){
            unset($_COOKIE['board_favorites'][$_POST['id']]);
            setcookie('board_favorites['.$_POST['id'].']', NULL, NULL, '/', '.'.$this->config['project']['host']);
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

        header('Content-type: image/png;');
        $id = $this->request->param('id');
        $ad = ORM::factory('BoardAd', $id);
        if($ad->loaded())
            echo FlyPhone::draw_canvas($ad->phone);
        else
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
                    $validation->rules('g-recaptcha-response', array(
                        array('Captcha::check', array(':value', ':validation', ':field'))
                    ));
                if($validation->check()){
                    Email::instance()
                        ->to($ad->email)
                        ->from($this->config['robot_email'])
                        ->subject($this->config['project']['name'] .': '. __('Message from bulletin board'))
                        ->message(View::factory('board/mail/user_mailto_letter', array(
                                'name' => $ad->name,
                                'email'=> Arr::get($_POST, 'email'),
                                'text'=> strip_tags(Arr::get($_POST, 'text')),
                                'site_name'=> $this->config['project']['name'],
                                'server_name'=> URL::base(KoMS::protocol()),
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
            $this->json['content'] = $this->getContentTemplate('board/user_mailto')->set(array(
                'errors' => $errors,
                'ad_id' => $ad->id,
            ))->render();
        }
    }

    /**
     * Отображение формы отправки сообщения (AJAX)
     */
    public function action_send_message(){
        if(!$this->request->is_ajax())
            $this->go(Route::get('board')->uri());
        $id = $this->request->param('id');
        $ad = ORM::factory('BoardAd', $id);

        $errors = array();

        if($ad->loaded()){
            $this->json['status'] = TRUE;
            if($this->request->method() == Request::POST){
                $validation = Validation::factory($_POST)
                    ->rule('text', 'not_empty')
                    ->rule('text', 'min_length', array(':value',10))
                    ->rule('text', 'max_length', array(':value',1000))
                    ->labels(array(
                        'email' => __('Your e-mail'),
                        'text' => __('Message text'),
                    ))
                ;
                $validation->rules('g-recaptcha-response', array(
                    array('Captcha::check', array(':value', ':validation', ':field'))
                ));
                if(!$this->logged_in){
                    $validation->rules('email', array(
                        array('not_empty'),
                        array('email'),
                    ));
                }

                try{
                    if(!$validation->check())
                        throw new Validation_Exception($validation);

                    /* Get users (new or existing an opponent) */
                    if(!$this->logged_in){
                        $userdata = Arr::extract($_POST, array('email'));
                        $user = ORM::factory('User')->where('email','=',$_POST['email'])->find();
                        if(!$user->loaded()){
                            /* registering new user */
                            $user = ORM::factory('User');
                            $userdata['password'] = substr(md5($userdata['email'] . time()), 0,7);
                            $user->create_user($userdata, array('email', 'username', 'password'));
                            $user->profile->user_id = $user->id;
                            $user->profile->values(array(
                                'name' => preg_replace('~@.*$~', '', Arr::get($_POST, 'email'))
                            ));
                            $user->profile->save();
                        }
                    }
                    else
                        $user = $this->current_user;

                    /* Creating dialog and message */
                    $dialog = Model_UserDialog::create_dialog($user->id, $user->profile->name, $ad->user->id, $ad->name, $ad->id, $ad->getTitle(), $ad->object_name());
                    $dialog->last_message_time = time();
                    $dialog->last_message_user = $user->id;
                    $dialog->update();
                    $dialog->addMessage($dialog->user_id, Arr::get($_POST, 'text'));

                    /* Notifying user about new messages */
                    if($dialog->opponent->loaded() && !$dialog->opponent->no_mails){
                        $message = View::factory('board/mail/user_message_notify', array(
                            'name' => $dialog->opponent_name,
                            'title'=> $dialog->subject,
                            'dialog_link'=> URL::base(KoMS::protocol()) . Model_User::generateCryptoLink('messaging', $dialog->opponent_id, array('dialog_id' => $dialog->id)),
                            'site_name'=> $this->config['project']['name'],
                            'server_name'=> URL::base(KoMS::protocol()),
                            'unsubscribe_link'=> URL::base(KoMS::protocol()) . Model_User::generateCryptoLink('unsubscribe', $dialog->opponent_id),
                        ))->render();
//                        file_put_contents(DOCROOT. '/debug_mail.txt', PHP_EOL.PHP_EOL. $message, FILE_APPEND);
                        Email::instance()
                            ->to($dialog->opponent->email)
                            ->from($this->config['robot_email'])
                            ->subject($this->config['project']['name'] .': '. __('Message from bulletin board'))
                            ->message($message, true)
                            ->send();
                    }
                    Flash::success(__("Your message successfully sended"));
                    if(!Auth::instance()->logged_in('login'))
                        $this->json['content'] = $this->getContentTemplate('board/user_outbox_done')->render();
                    else
                        $this->json['content'] .= View::factory('inbox/goto_dialog')->set(array(
                            'dialog_link' => Route::get('messaging')->uri(array(
                                'action' => 'dialog',
                                'id' => $dialog->id,
                            ))
                        ))->render();
                    return;
                }
                catch(Validation_Exception $e){
                    $errors = $validation->errors('error/validation');
                }
                catch(ORM_Validation_Exception $e){
                    $errors = $validation->errors('error/validation');
                }
            }
            $this->json['content'] = $this->getContentTemplate('board/user_outbox')->set(array(
                'errors' => $errors,
                'ad_id' => $ad->id,
            ))->render();
        }
        else{
            $this->json['content'] = $this->getContentTemplate('board/user_outbox')->set(array(
                'errors' => array(__('Nothing found')),
                'ad_id' => NULL,
            ))->render();
        }
    }

    public function action_pagemoved(){
        $id = Request::$current->param('id');
        $catid = Request::$current->param('cat_id');
        if(!is_null($id)){
            $ad = ORM::factory('BoardAd', $id);
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".($ad->loaded() ? URL::base() . $ad->getUri() : '/'));
            exit();
        }
        elseif(!is_null($catid)){
            $cat = ORM::factory('BoardCategory', $catid);
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".($cat->loaded() ? $cat->getUri(BoardConfig::instance()->country_alias) : '/'));
            exit();
        }
        throw new HTTP_Exception_404('Эта страница устарела и перенесена');
    }

    public function action_autocomplete(){
        if(!Request::current()->is_ajax())
            throw new HTTP_Exception_403();
        $query = Arr::get($_GET, 'term', false);
        if($query !== false){
            $category_alias = Arr::get($_GET, 'category', false);
            $category_id = ($category_alias ? Model_BoardCategory::getCategoryIdByAlias($category_alias) : 0);
            $tags = DB::select()->from(ORM::factory('BoardSearch')->table_name())->where('query','LIKE', $query.'%')->and_where('category_id', '=', $category_id)->order_by('cnt','DESC')->limit(10)->execute();
            $this->json['status'] = true;
            foreach($tags as $_tag){
                $this->json[] = array(
                    'id' => 'tag_'.$_tag['id'],
                    'label' => $_tag['query'],
                    'value' => $_tag['query'],
                );
            }
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

        $content = $this->getContentTemplate('board/form_filters_ajax');
        return $content->set(array('filters' => $filters))->render();
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
            asort($options);
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
            asort($options);
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
//            if(!count($options))
//                $parameters['disabled'] = 'disabled';
            if(count($options))
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
            ->from($this->config['robot_email'])
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


	/**
	 * Добавляет META теги для страницы поиска
	 * @param $page_data
	 */
    protected function _addPageData($page_data){
    	// Meta tags
    	if(isset($page_data['meta_add']) && is_array($page_data['meta_add']))
		    foreach ($page_data['meta_add'] as $_meta)
			    $this->add_meta_content($_meta);
	    // Canonical pagination
	    if(isset($page_data['meta_canonical']) && is_array($page_data['meta_canonical']))
		    foreach ($page_data['meta_canonical'] as $_canonical)
			    $this->add_meta_content($_canonical);
	    // Meta tags replacement
	    if(isset($page_data['meta_replace']) && is_array($page_data['meta_replace']))
		    foreach ($page_data['meta_replace'] as $_meta_id=>$_meta)
			    $this->replace_meta_content($_meta_id, $_meta);
	    // Breadcrumbs
	    if(isset($page_data['breadcrumbs']) && is_array($page_data['breadcrumbs'])){
		    $this->breadcrumbs = Breadcrumbs::factory();
		    $this->breadcrumbs->setOption('addon_class', 'bread_crumbs_search');
		    foreach ($page_data['breadcrumbs'] as $_crumb)
			    $this->breadcrumbs->add($_crumb[0], $_crumb[1]);
	    }

	    // Title & description
	    if(isset($page_data['titles'])){
		    if(isset($page_data['titles']['title']))
			    $this->title = $page_data['titles']['title'];
		    if(isset($page_data['titles']['description']))
			    $this->description = $page_data['titles']['description'];

		    if($this->template->content instanceof View){
			    if(isset($page_data['titles']['pagetitle']))
				    $this->template->content->set('title', $page_data['titles']['pagetitle']);
			    if(isset($page_data['titles']['subtitle']))
				    $this->template->content->set('subtitle', $page_data['titles']['subtitle']);
			    if(isset($page_data['titles']['nothing_found_text']))
				    $this->template->content->set('nothing_found_text', $page_data['titles']['nothing_found_text']);
		    }
	    }
    }
}