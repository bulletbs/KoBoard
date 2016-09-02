<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 */

class Controller_UserBoard extends Controller_User
{
    public $auth_required = 'login';

    public $company;

    public $skip_auto_content_apply = array(
        'enable',
        'remove',
        'refresh',
        'refresh_all',
        'multi',
        'notice_remove',
        'notice_clean',
    );

    public function before(){
        /* Путь к шаблону */
        $this->uri = 'board/cabinet/'. $this->request->action();
        $this->styles[] = "assets/board/css/board.css";
        parent::before();

        if(Auth::instance()->logged_in('company')){
            $company = ORM::factory('CatalogCompany')->where('user_id', '=', $this->current_user->id)->find();
            if($company->loaded())
                $this->company = $company;
        }
    }


    /**
     * Companies list action
     */
    public function action_list(){
        $ads = ORM::factory('BoardAd')->where('user_id', '=', $this->current_user->id)->order_by('addtime', 'DESC')->find_all()->as_array('id');
        $this->user_content->set(array(
            'ads' => $ads,
            'photos' => Model_BoardAdphoto::adsPhotoList(array_keys($ads)),
        ));
        $this->right_contents = View::factory('board/cabinet/legend');
    }

    /**
     * Ad add & edit action
     */
    public function action_edit(){
        $errors = array();
        $id = $this->request->param('id');
        $this->breadcrumbs->add(__('My ads'), URL::site().Route::get('board_myads')->uri());

        if(is_null($id) && BoardConfig::instance()->addnew_suspend === TRUE){
            $this->user_content = $this->getContentTemplate('board/add_suspended');
            return;
        }

        $ad = ORM::factory('BoardAd')->where('id', '=', $id)->and_where('user_id', '=', $this->current_user->id)->find();
        $photos = $ad->photos->find_all();
        if($id > 0 && !$ad->loaded())
            $this->redirect(URL::site().Route::get('board_myads')->uri());
        elseif(is_null($id)){
            $ad->values( $this->current_user->as_array() );
            $ad->values( $this->current_user->profile->as_array() );
            if($this->current_user->profile->city_id > 0 ){
                $ad->values(array(
                    'pcity_id' => ORM::factory('BoardCity', $this->current_user->profile->city_id)->parent_id,
                ));
            }
        }

        if(HTTP_Request::POST == $this->request->method()){
            if(Arr::get($_POST, 'cancel'))
                $this->redirect(URL::site().Route::get('board_myads')->uri());

            $ad->category_id =  Arr::get($_POST, 'category_id');
            $ad->values($_POST);
            $ad->user_id = $this->current_user->id;
            $ad->email = $this->current_user->email;
        // company data
            if($this->company instanceof Model_CatalogCompany && $this->company->loaded()){
                $ad->type = (int) Auth::instance()->logged_in('company');
                $ad->company_id = $this->company->id;
            }
            else{
                $ad->type = in_array($ad->category_id, Model_BoardCategory::getJobIds()) ? Arr::get($_POST, 'type', 0) : 0;
                $ad->company_id = 0;
            }

            if(!$ad->loaded())
                $ad->publish = 1;

            try{
                $validation = Validation::factory($_POST);
                if(!$ad->loaded())
                    $validation
                        ->rules('termagree', array(
                            array('Model_BoardAd::checkAgree', array(':value', ':validation', ':field'))
                        ));

                if(!$ad->loaded() && Model_BoardAd::checkFrequentlyAdded()){
                    $validation
                        ->rules('captcha', array(
                            array('not_empty'),
                            array('Captcha::checkCaptcha', array(':value', ':validation', ':field'))
                        ))
                        ->labels(array(
                            'captcha' => __('Enter captcha code'),
                        ));
                }
                $ad->save($validation);

                $files = Arr::get($_FILES, 'photos', array('tmp_name' => array()));
            /* Check for big photos */
                if(in_array(UPLOAD_ERR_INI_SIZE, $files['error'])){
                    foreach($files['error'] as $_file_id=>$_error)
                        if($_error == UPLOAD_ERR_INI_SIZE){
                            Flash::warning(__('File :file too big to be uploaded (max=:max bytes)', array(':file'=>$files['name'][$_file_id], ':max'=>ini_get('upload_max_filesize'))));
                        }
                }
            //Save photos
                foreach ($files['tmp_name'] as $k => $file) {
                    $ad->addPhoto($file);
                }
            //Deleting photos
                $files = Arr::get($_POST, 'delphotos', array());
                foreach ($files as $file_id)
                    $ad->deletePhoto($file_id);
            //Setting up main photo
                $setmain = Arr::get($_POST, 'setmain');
                $ad->setMainPhoto($setmain);
            //FILTERS */
                $filters = Arr::get($_POST, 'filters');
                $ad->saveFilters($filters);
            //Company categories
                if($this->company instanceof Model_CatalogCompany && $this->company->loaded())
                    Model_CatalogCompany::updateCompanyCategories($this->company->id);

                if(!is_null($id))
                    Flash::success(__('Your ad successfully saved'));
                else
                    Flash::info($this->getContentTemplate('board/published')->set('ad', $ad)->render());
                $this->redirect(URL::site().Route::get('board_myads')->uri());
            }
            catch(ORM_Validation_Exception $e){
                $errors = $e->errors('validation');
            }
        }

        $this->user_content->set('cities', ORM::factory('BoardCity')->getTwoLevelArray());

        /* Если была выбрана категория - загружаем дерево категорий и фильтры */
        $subcategories = '';
        $filters = '';
        if($ad->category_id > 0){
            $filters = $this->_render_filters_list($ad->category, $ad->id);
        }

        /* Категории и фильтры */
        $categories_main = array(''=>"Выберите категорию");
        $categories_main += ORM::factory('BoardCategory')->where('parent_id', '=', 0)->cached(Model_BoardCategory::CATEGORIES_CACHE_TIME)->find_all()->as_array('id','name');

        $filters = '';
        $cat_child = '';
        if($ad->category_id > 0){
            $cat_child = $this->_render_subcategory_list($ad->category->parent(), $ad->category_id);
            $filters = $this->_render_filters_list($ad->category, $ad->id);
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

        if($this->is_mobile){
            $this->mobile_scripts[] = "media/libs/jquery-input-limit/jquery.limit-1.2.source.js";

            $this->mobile_scripts[] = "media/libs/poshytip-1.2/jquery.poshytip.min.js";
            $this->mobile_styles[] = "media/libs/poshytip-1.2/tip-yellowsimple/tip-yellowsimple.css";
            $this->mobile_scripts[] = "assets/board/js/form.js";
        }
        else {
            $this->scripts[] = "media/libs/poshytip-1.2/jquery.poshytip.min.js";
            $this->styles[] = "media/libs/poshytip-1.2/tip-yellowsimple/tip-yellowsimple.css";
            $this->scripts[] = "assets/board/js/form.js?ver=1.1";
            $this->styles[] = "media/libs/jquery-form-styler/jquery.formstyler.css";
            $this->scripts[] = "media/libs/jquery-form-styler/jquery.formstyler.min.js";
            $this->scripts[] = "media/libs/jquery-input-limit/jquery.limit-1.2.source.js";
        }

        $this->user_content->set(array(
            'user' => $this->current_user,
            'model' => $ad,
            'photos' => $photos,
            'errors' => $errors,
            'units_options' => BoardConfig::instance()->unitsOptions(),

            'categories_main' => $categories_main,
            'cat_child' => $cat_child,
            'filters' => $filters,

            'regions' => $regions,
            'cities' => $cities,
            'job_ids' => Model_BoardCategory::getJobIds(),
            'noprice_ids' => Model_BoardCategory::getNopriceIds(),
        ));
    }

    /**
     * Ad enable/disable action
     */
    public function action_enable(){
        $id = $this->request->param('id');
        $model = ORM::factory('BoardAd')->where('id', '=', $id)->and_where('user_id', '=', $this->current_user->id)->find();
        if($id > 0 && !$model->loaded()){
            $this->redirect(URL::site().Route::get('board_myads')->uri());
            Flash::warning(__('Ad not found'));
        }
        else{
            try{
                $model->flipStatus();
                Flash::success(__('Your ad successfully turned '. (!$model->publish ? 'off' : 'on')));
                if($this->company instanceof Model_CatalogCompany && $this->company->loaded())
                    Model_CatalogCompany::updateCompanyCategories($this->company->id);
            }
            catch(ORM_Validation_Exception $e){
                $errors = $e->errors('validation', TRUE);
                Flash::error('- ' . implode("<br>- ", $errors));
            }
            $this->redirect(URL::site().Route::get('board_myads')->uri());

        }
    }

    /**
     * Ad remove action
     */
    public function action_remove(){
        $id = $this->request->param('id');
        $model = ORM::factory('BoardAd')->where('id', '=', $id)->and_where('user_id', '=', $this->current_user->id)->find();
        if($id > 0 && !$model->loaded()){
            $this->redirect(URL::site().Route::get('board_myads')->uri());
            Flash::warning(__('Ad not found'));
        }
        else{
            $model->delete();
            Flash::success(__('Your ad has been removed'));
            if($this->company instanceof Model_CatalogCompany && $this->company->loaded())
                Model_CatalogCompany::updateCompanyCategories($this->company->id);
            $this->redirect(URL::site().Route::get('board_myads')->uri());
        }
    }

    /**
     * Ad refresh action
     */
    public function action_refresh(){
        $id = $this->request->param('id');
        $model = ORM::factory('BoardAd')->where('id', '=', $id)->and_where('user_id', '=', $this->current_user->id)->find();
        if($id > 0 && !$model->loaded()){
            Flash::warning(__('Ad not found'));
        }
        elseif($model->addtime > time() - Date::DAY){
            Flash::warning(__('You can update your ads only once a week'));
        }
        else{
            try{
                $model->refresh();
                Flash::success(__('Your ad has been refreshed'));
            }
            catch(ORM_Validation_Exception $e){
                $errors = $e->errors('validation', TRUE);
                Flash::error('- ' . implode("<br>- ", $errors));
            }
        }
        $this->redirect(URL::site().Route::get('board_myads')->uri());
    }

    /**
     *  Renew all ads
     */
    public function action_refresh_all(){
        $ads = ORM::factory('BoardAd')
            ->where('user_id', '=', $this->current_user->id)
            ->and_where('publish', '=', 1)
            ->and_where('addtime', '<', time() - Model_BoardAd::REFRESH_TIME)
            ->find_all()
        ;
        try{
            foreach($ads as $model)
                $model->refresh();
            Flash::success(__('Your ads has been refreshed'));
        }
        catch(ORM_Validation_Exception $e){
            $errors = $e->errors('validation', TRUE);
            Flash::error('- ' . implode("<br>- ", $errors));
        }
        $this->redirect(URL::site().Route::get('board_myads')->uri());
    }

    /**
     * Operates multi-operations with checked ads
     */
    public function action_multi(){

        $this->redirect( Request::current()->referrer() );
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
            if(!count($options))
                $parameters['disabled'] = 'disabled';
            $content = Form::select('filters['.$id.']', $options, $selected, $parameters);
        }
        return $content;
    }

    public function action_notices(){
        $notices = ORM::factory('BoardNotice')->where('user_id', '=', $this->current_user->id)->find_all();
        DB::update(ORM::factory('BoardNotice')->table_name())->set(array('received'=>1))->where('user_id','=',$this->current_user)->execute();
        $this->user_content->set(array(
            'notices' => $notices,
        ));
    }

    public function action_notice_remove(){
        $id = $this->request->param('id');
        if(is_null($id))
            throw new HTTP_Exception_404();
        $notice = ORM::factory('BoardNotice', $id);
        if(!$notice->loaded() || $notice->user_id != $this->current_user->id)
            throw new HTTP_Exception_404();
        $notice->delete();
        Flash::success(__('Notice successfully removed'));
        $this->redirect(Route::get('board_notices')->uri());
    }

    public function action_notice_clean(){
        $table = ORM::factory('BoardNotice')->table_name();
        DB::delete($table)->where('user_id', '=', $this->current_user->id)->execute();
        Flash::success(__('Your notices cleaned'));
        $this->redirect(Route::get('board_notices')->uri());
    }
}