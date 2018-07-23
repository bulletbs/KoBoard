<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Widgets_BoardTags extends Controller_System_Widgets {

    const TAGS_CACHE = 'BoardTags_';

    public $skip_auto_content_apply = array(
//        'index',
    );

    public $template = 'widgets/board_tags';    // Шаблон виждета

    /**
     * Search form output
     */
    public function action_index()
    {
        $title = Request::current()->post('title');
        if(!is_null($title)){
            $tags = $this->_tagsForAd($title);
            $terms = $this->_termsForAd($title);
        }
        else{
            $tags = $this->_tagsForCategory();
            $terms = Request::current()->post('terms');
        }
        $this->template->set('tags', $tags);
        $this->template->set('terms', $terms);
        $this->response->body($this->template);
    }

    protected function _tagsForCategory(){
        $tags = array();
        $category = Request::initial()->param('cat_alias');
        if(!is_null($category))
            $category_id = Model_BoardCategory::getCategoryIdByAlias($category);
        else
            $category_id = 0;
        $tags = ORM::factory('BoardSearch')->where('category_id', '=', $category_id)->and_where('cnt','>', 10)->cached(Date::DAY)->order_by('cnt','DESC')->limit(15)->find_all()->as_array('id');
        return array_values($tags);
    }

    protected function _tagsForAd($title){
        $tags = array();
        $category_id = Request::current()->post('category_id');
        $pcategory_id = Request::current()->post('pcategory_id');
        $tags = ORM::factory('BoardSearch')->select(DB::expr('DISTINCT `query` dstq'), DB::expr('SUM(cnt) scnt'))->where('category_id', '=', $category_id)->or_where('category_id', '=', $pcategory_id)->cached(Date::DAY)->group_by('dstq')->order_by('scnt', 'DESC')->find_all()->as_array('id');
        $title = mb_strtolower($title);
        foreach($tags as $tagid=>$tag){
            if(!mb_strstr($title, mb_strtolower($tag->query)))
                unset($tags[$tagid]);
        }
        return array_values($tags);
    }

    protected function _termsForAd($title){
        $terms = BoardTerms::seokeywords($title, 5, 5);
        return $terms;
    }
}