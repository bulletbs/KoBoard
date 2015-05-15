<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 */

class Controller_UserBoard extends Controller_User
{
    public $auth_required = 'login';

    public $skip_auto_content_apply = array(
        'enable',
        'remove',
    );

    public function before(){
        /* Путь к шаблону */
        $this->uri = 'board/cabinet/'. $this->request->action();
        parent::before();
        $this->board_cfg = Kohana::$config->load('board')->as_array();
    }


    /**
     * Companies list action
     */
    public function action_list(){
        $ads = ORM::factory('BoardAd')->where('user_id', '=', $this->current_user->id)->find_all();
        $this->user_content->set(array(
            'ads' => $ads,
        ));
    }

    /**
     * Ad add & edit action
     */
    public function action_edit(){
        $this->scripts[] = "assets/board/js/form.js";

        $errors = array();
        $id = $this->request->param('id');
        $this->breadcrumbs->add(__('My ads'), URL::site().Route::get('board_myads')->uri());

        $ad = ORM::factory('BoardAd')->where('id', '=', $id)->and_where('user_id', '=', $this->current_user->id)->find();
        $photos = $ad->photos->find_all();
        if($id > 0 && !$ad->loaded())
            $this->redirect(URL::site().Route::get('board_myads')->uri());

        if(HTTP_Request::POST == $this->request->method()){
            if(Arr::get($_POST, 'cancel'))
                $this->redirect(URL::site().Route::get('board_myads')->uri());

            $ad->category_id =  Arr::get($_POST, 'category_id');

            $ad->values($_POST);
            $ad->user_id = $this->current_user->id;
            try{
                $ad->save();

                /* Save photos */
                $files = Arr::get($_FILES, 'photos', array('tmp_name' => array()));
                foreach ($files['tmp_name'] as $k => $file) {
                    $ad->addPhoto($file);
                }

                /* Deleting photos */
                $files = Arr::get($_POST, 'delphotos', array());
                foreach ($files as $file_id)
                    $ad->deletePhoto($file_id);

                /* Setting up main photo */
                $setmain = Arr::get($_POST, 'setmain');
                $ad->setMainPhoto($setmain);
                $filters = Arr::get($_POST, 'filters');
                $ad->saveFilters($filters);

                Flash::success(__('Your ad successfully saved'));
                $this->redirect(URL::site().Route::get('board_myads')->uri());
            }
            catch(ORM_Validation_Exception $e){
                $errors = $e->errors('validation');
            }
        }

        $categories = array(''=>"Выберите категорию");
        $categories += ORM::factory('BoardCategory')->getTwoLevelArray();
        $this->user_content->set('categories', $categories);
        $this->user_content->set('cities', ORM::factory('BoardCity')->getTwoLevelArray());
        $this->user_content->set('price_value', $this->board_cfg['price_value']);

        /* Если была выбрана категория - загружаем дерево категорий и фильтры */
        $subcategories = '';
        $filters = '';
        if($ad->category_id > 0){
            $filters = $this->_render_filters_list($ad->category, $ad->id);
        }

        $this->user_content->set(array(
            'user' => $this->current_user,
            'model' => $ad,
            'photos' => $photos,
            'errors' => $errors,
            'filters' => $filters,
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
            Flash::success(__('Your ad successfully turned '. ($model->publish ? 'off' : 'on')));
            $model->flipStatus();
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
            $this->redirect(URL::site().Route::get('board_myads')->uri());

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
}