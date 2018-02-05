<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Created by JetBrains PhpStorm.
 * User: butch
 * Date: 23.05.12
 * Time: 18:35
 * To change this template use File | Settings | File Templates.
 */
class Controller_Admin_BoardCategoryMove extends Controller_System_Admin
{
    public $skip_auto_render = array(
        'move',
        'backup',
    );

    public $skip_auto_content_apply = array(
        'index',
    );

    public function before(){
        parent::before();

        if($this->auto_render)
            $this->template->submenu= Widget::factory('adminBoardMenu')->render();
    }

    /**
     * List items
     */
    public function action_index(){
        /* Save category compares */
        if(isset($_POST['save'])){
			/* Сопоставление главных фильтров */
	        Database::instance()->query(Database::SELECT, "TRUNCATE TABLE ad_move_filter2category");
            if(count($movefilterto = Arr::get($_POST, 'move_filter_to')))
                foreach($movefilterto as $_filter_id=>$_filters){
	                foreach($_filters as $_option_id=>$_new_id){
	                	if($_new_id > 0)
			                DB::insert('ad_move_filter2category', array('filter_id', 'option_id', 'new_id'))->
			                values(array(
			                    $_filter_id,
			                    $_option_id,
			                    $_new_id,
			                ))->execute();
	                }
                }
            /* Сопоставление категорий */
            if(count($moveto = Arr::get($_POST, 'move_to')))
                foreach($moveto as $_category_id=>$_new_id)
                    DB::update(ORM::factory('BoardCategoryTemp')->table_name())->value('new_id', $_new_id)->value('moved', 0)->where('id','=',$_category_id)->execute();
        }

        /* Move categories to temp table */
        if(isset($_POST['backup'])){
            $table = ORM::factory('BoardCategory')->table_name();
            $temp_table = ORM::factory('BoardCategoryTemp')->table_name();
            Database::instance()->query(Database::SELECT, "TRUNCATE TABLE ".$temp_table);
            Database::instance()->query(Database::INSERT, "INSERT INTO ".$temp_table." ".DB::select('*', 'id')->from($table));
            DB::delete($table)->execute();
            Flash::success('Категории успешно перенесены во временную таблицу');
            Flash::warning('Внимание! Основная таблица категорий очищена для наполнения');
        }

        /* Move ads to new categories */
        if(isset($_POST['move'])){
			// загрузка связей
	        $new_parents = ORM::factory('BoardCategory')->where('parent_id', '>', 0)->find_all()->as_array('id', 'parent_id');
	        $old_categories = ORM::factory('BoardCategoryTemp')->where('moved','=', 0)->order_by('new_id', 'ASC')->find_all()->as_array('id', 'new_id');

	        // сдвиг старых категорий
	        if(count($old_categories)){
		        $stepup = -1;
		        DB::update('ads')
		          ->value('category_id', DB::expr('category_id*'.$stepup))
		          ->execute();
	        }

	        // перенос по категориям
	        foreach($old_categories as $_id=>$_new_id){
            	if($_new_id > 0){
		            DB::update(ORM::factory('BoardAd')->table_name())
		              ->and_where('category_id', '=', $_id*$stepup)
		              ->set(array(
			              'category_id' => $_new_id,
			              'pcategory_id' => $new_parents[$_new_id],
		              ))
		              ->execute();
	            }
            	else{
		            DB::delete(ORM::factory('BoardAd')->table_name())
		              ->where('category_id', '=', $_id*$stepup)
		              ->execute();
	            }


                DB::update(ORM::factory('BoardCategoryTemp')->table_name())->value('moved', 1)->where('id','=',$_id)
                ->execute();
            }

	        // перенос по фильтрам
	        $filters = DB::select()->from('ad_move_filter2category')->where('moved','=', 0)->execute();
	        foreach ($filters as $_move_filter){
		        Database::instance()->query(Database::UPDATE, "UPDATE ads a INNER JOIN ad_filter_values afv ON a.id=afv.ad_id SET a.category_id='".$_move_filter['new_id']."', a.pcategory_id='".$new_parents[$_move_filter['new_id']]."' WHERE afv.filter_id='".$_move_filter['filter_id']."' AND afv.value='".$_move_filter['option_id']."'");
		        DB::update('ad_move_filter2category')->value('moved', 1)->where('filter_id','=',$_move_filter['filter_id'])->and_where('option_id','=', $_move_filter['option_id'])->execute();
	        }
        }

        $this->scripts[] = "media/libs/bootstrap/js/bootbox.min.js";
        $this->scripts[] = "media/libs/bootstrap/js/bbox_".I18n::$lang.".js";
        $this->scripts[] = "media/js/admin/check_all.js";

        $categories = ORM::factory('BoardCategory')->getFullDepthArray();
        $categories_options = Arr::merge(array('0' => '..'), ORM::factory('BoardCategory')->getFullDepthArray());

        $old_categories = ORM::factory('BoardCategoryTemp')->fulltree();

        $main_filters = array();
        $filters = ORM::factory('BoardFilter')->where('main', '=', 1)->find_all()->as_array('id');
        foreach ($filters as $_filter_id=>$_filter){
	        $main_filters[$_filter->category_id] = array(
	        	'id' => $_filter_id,
	        	'name' => $_filter->name,
	        );
	        $main_filters[$_filter->category_id]['options'] = ORM::factory('BoardOption')->where('filter_id', '=', $_filter->id)->find_all()->as_array('id', 'value');
	        $main_filters[$_filter->category_id]['values'] = DB::select()->from('ad_move_filter2category')->where('filter_id','=',$_filter_id)->execute()->as_array('option_id','new_id');
        }

        $this->template->content = View::factory('admin/categories/board_category_compare')
            ->set('categories', $categories)
            ->set('categories_options', $categories_options)
            ->set('old_categories', $old_categories)
            ->set('main_filters', $main_filters)
        ;
    }
}
