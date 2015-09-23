<?php defined('SYSPATH') OR die('No direct script access.');?>
<div class="alcenter">
    <?php echo Widget::factory('Banner970x90')?>
</div>
    <div class="line"></div>
<h1><?php echo $title ?></h1>
<?if(!$search_by_user):?>
<?if(isset($city_counter)):?>
    <div class="line"></div>
    <span class="showcity">
    <a href="#" id="showBigCity" class="active">крупные города</a>
    <a href="#" id="showAllCity">все</a>
    </span>
    <ul class="search_sub_row" id="city_list"><?$_step = ceil(count($city_counter)/5)?>
    <?for($_r = 0; $_r < $_step; $_r++):?>
    <?for($_c = 0; $_c < $_step*5; $_c+=$_step):?>
    <?if(isset($city_counter[$_r+$_c])):?><li <?php echo $city_counter[$_r+$_c]['cnt']>100 ? 'class="bigcity"' : 'class="smallcity"'?>><?php echo HTML::anchor(Model_BoardCity::generateUri( Model_BoardCity::getField('alias', $city_counter[$_r+$_c]['city_id'])), Model_BoardCity::getField('name', $city_counter[$_r+$_c]['city_id']) . ' <span>' .$city_counter[$_r+$_c]['cnt']. '</span>') ?></li><?else:?><li class="smallcity"></li><?endif;?>
    <?endfor;?>
    <?endfor;?></ul>
    <div class="clear"></div>
<?endif?>
<?if(count($childs_categories)):?>
    <div class="line"></div>
    <ul class="search_sub_row search_sub_row_<?php echo $childs_categories_col?>"><?$_step = ceil(count($childs_categories)/$childs_categories_col)?>
    <?for($_r = 0; $_r < $_step; $_r++):?>
    <?for($_c = 0; $_c < $_step*$childs_categories_col; $_c+=$_step):?>
    <?if(isset($childs_categories[$_r+$_c])):?><li><?php echo HTML::anchor(Model_BoardCategory::generateUri($childs_categories[$_r+$_c]->alias), $childs_categories[$_r+$_c]->name) ?></li><?else:?><li></li><?endif?>
    <?endfor;?>
    <?endfor;?></ul>
    <div class="clear"></div>
<?endif?>
<?if(isset($main_filter)):?>
    <script type="text/javascript">
        var basecat_uri = '<?php echo $main_filter['base_uri'] ;?>';
        var subcat_options = <?php echo json_encode(array_flip($main_filter['aliases'])) ?>;
    </script>
    <div class="line"></div>
    <ul class="search_sub_row"><?$_step = ceil(count($main_filter['options'])/5)?>
        <?for($_r = 0; $_r < $_step; $_r++):?>
            <?for($_c = 0; $_c < $_step*5; $_c+=$_step):?>
                <?if(isset($main_filter['options'][$_r+$_c])):?><li><?php echo HTML::anchor(Model_BoardFilter::generateUri($main_filter['options'][$_r+$_c]['alias']), $main_filter['options'][$_r+$_c]['value']) ?></li><?else:?><li></li><?endif?>
            <?endfor;?>
        <?endfor;?></ul>
    <div class="clear"></div>
<?endif?>
<div class="line"></div>
<?endif?>
<div class="col_main col_type_selection">
    <?if(!$search_by_user):?><div class="type_selector">
        <?php echo HTML::anchor(Request::$current->uri() . URL::query(array('type'=>NULL)), __('Any'), array('class' => is_null(Arr::get($_GET, 'type')) ? 'active':''))?>
        <?php echo HTML::anchor(Request::$current->uri() . URL::query(array('type'=>Model_BoardAd::PRIVATE_TYPE)), __(!is_null($category) && $category instanceof ORM && $category->job ? 'Resume' : 'Private'), array('class' => Arr::get($_GET, 'type')=== (string) Model_BoardAd::PRIVATE_TYPE ? 'active':''))?>
        <?php echo HTML::anchor(Request::$current->uri() . URL::query(array('type'=>Model_BoardAd::BUSINESS_TYPE)), __(!is_null($category) && $category instanceof ORM && $category->job ? 'Vacancy' : 'Business'), array('class' => Arr::get($_GET, 'type')=== (string) Model_BoardAd::BUSINESS_TYPE? 'active':''))?>
        <div class="clear"></div>
    </div><?endif?>
    <?if(count($ads)):?>
    <table class="tableccat" id="adList">
    <?foreach($ads as $ad):?>
    <tr><td class="dashed">
    <table>
    <tr>
    <td class="list_date"><?= Date::smart_date($ad->addtime)?><br><b><?= date('G:i', $ad->addtime) ?></b><a href="#" class="ico_favorite" data-item="<?=$ad->id?>" title="Добавить в избранное"></a></td>
    <td class="list_img"><?if(isset($photos[$ad->id])):?><img src="<?php echo $photos[$ad->id]->getThumbUri()?>"><?else:?><img alt=<?php echo $ad->title?>" src="/assets/board/css/images/noimage.png"/><?endif?></td>
    <td class="list_title"><h3><?php echo HTML::anchor($ad->getUri(), $ad->title, array('title'=> $ad->title))?></h3> <?php echo $ad->getShortDescr() ?><br> <span class="quiet"><?php echo Model_BoardCity::getField('name', $ad->city_id)?><br><?php echo Model_BoardCategory::getField('name', $ad->category_id)?></span> </td>
    <td class="list_price"><?= ($ad->price > 0 ? $ad->price.' '.$cfg['price_value'] : '') ?></td>
    </tr>
    </table>
    </td></tr>
    <?endforeach;?>
    </table>
    <div class="clear"></div>
    <?php echo $pagination->render()?>
    <?else:?>
     <b>К сожалению не удалось найти объявления по указаным критериям</b>
    <?endif?>
</div>
<div class="col_tools">
<div class="alcenter">
    <?php echo Widget::factory('Banner240x400')?>
</div>
    <br>
    <div class="alcenter">
            <script type="text/javascript" src="//yandex.st/share/share.js"
                    charset="utf-8"></script>
            <div class="yashare-auto-init" data-yashareL10n="ru"
                 data-yashareQuickServices="yaru,vkontakte,facebook,twitter,odnoklassniki,moimir" data-yashareTheme="counter"
                ></div>
        </div>
        <div class="clear"></div>

</div>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  