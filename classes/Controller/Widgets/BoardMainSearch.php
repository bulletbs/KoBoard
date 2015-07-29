<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Widgets_BoardMainSearch extends Controller_System_Widgets {

    public $template = 'widgets/board_main_search_form';    // Шаблон виждета

    /**
     * Search form output
     */
    public function action_index()
    {
        $ads_count = Model_BoardAd::countActiveAds();
        $ads_count = preg_replace('~(\d)(?=(\d\d\d)+([^\d]|$))~', "$1 ", $ads_count);
        $this->template->set(array(
            'ads_count' => $ads_count,
        ));
    }

}