<?php defined('SYSPATH') OR die('No direct script access.');?>

<h1 class="category_small"><?echo $title?></h1>
<?if($search_by_user):?>
    <div class="line_nobg"></div>
    <?php echo Widget::factory('Banner', array('tpl'=>'banner728x90_search'))?>
    <div class="line_nobg"></div>
<?endif?>
<?if(!$search_by_user):?>
<?if(count($childs_categories)):?>
    <ul class="search_sub_col search_sub_col_4"><?$_step = ceil(count($childs_categories)/4)?>
    <?foreach($childs_categories as $_k=>$_category):?><?if($_k>0 && $_k%$_step==0):?></ul><ul class="search_sub_col search_sub_col_4"><?endif?>
    <li><?php echo HTML::anchor(Model_BoardCategory::generateUri( Model_BoardCategory::getField('alias', $_category->id)), Model_BoardCategory::getField('name', $_category->id) . (isset($category_counter[$_category->id]) ? ' <span>' .$category_counter[$_category->id] : ''). '</span>') ?></li>
    <?endforeach?>
    </ul>
    <div class="clear"></div>
<?endif?>
<?if(isset($main_filter)):?>
    <?if(count($main_filter['options'])):?>
    <div class="line_nobg"></div>
    <ul class="search_sub_col"><?$_step = ceil(count($main_filter['options'])/5)?>
    <?foreach($main_filter['options'] as $_opt_id=>$_opt):?><?if($_opt_id>0 && $_opt_id%$_step==0):?></ul><ul class="search_sub_col"><?endif?>
    <li><?php echo HTML::anchor(Model_BoardFilter::generateUri($_opt['alias']), $_opt['value']) ?></li>
    <?endforeach?></ul>
    <div class="clear"></div>
    <?endif?>
<?endif?>
<?if(isset($city_counter)):?>
    <div class="line"></div>
    <span class="showcity">
<a href="#" id="showBigCity" class="active">крупные <?php echo !$city instanceof ORM || !$city->parent_id ? 'регионы' : 'города'?></a>
<a href="#" id="showAllCity">все</a>
</span>
    <div id="big_city_list">
        <ul class="search_sub_col search_sub_col_4"><?$_step = ceil(count($big_city_counter)/4); $_i=0;?>
        <?foreach($big_city_counter as $_city_id=>$_city):?><?if($_i>0 && $_i%$_step==0):?></ul><ul class="search_sub_col search_sub_col_4"><?endif?>
        <li><?php echo HTML::anchor(Model_BoardCity::generateUri( Model_BoardCity::getField('alias', $_city_id)), Model_BoardCity::getField('name', $_city_id) . ' <span>' .$_city. '</span>') ?></li>
        <?$_i++?><?endforeach?>
        </ul>
    </div>
    <div id="all_city_list" style="display:none;">
        <ul class="search_sub_col search_sub_col_4"><?$_step = ceil(count($city_counter)/4); $_i=0;?>
        <?foreach($city_counter as $_city_id=>$_city):?><?if($_i>0 && $_i%$_step==0):?></ul><ul class="search_sub_col search_sub_col_4"><?endif?>
        <li><?php echo HTML::anchor(Model_BoardCity::generateUri( Model_BoardCity::getField('alias', $_city_id)), Model_BoardCity::getField('name', $_city_id) . ' <span>' .$_city. '</span>') ?></li>
        <?$_i++?><?endforeach?>
        </ul>
    </div>
    <div class="clear"></div>
<?endif?>
<div class="line_nobg"></div>
    <?php echo Widget::factory('Banner', array('tpl'=>'banner728x90_search'))?>
<div class="line_nobg"></div>
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
    <td class="list_img"><?= HTML::anchor( $ad->getUri(), HTML::image(isset($photos[$ad->id]) ? $photos[$ad->id]->getThumbUri() : "/assets/board/css/images/noimage.png", array('alt'=>htmlspecialchars($ad->getTitle()), 'title'=>htmlspecialchars($ad->getTitle()))) . ($ad->photo_count ? '<span title="Всего фотографий: '.$ad->photo_count.'">'.$ad->photo_count.'</span>' : ''))?></td>
    <td class="list_fav"><a href="#" class="ico_favorite" data-item="<?=$ad->id?>" title="Добавить в избранное"></a></td>
    <td class="list_title">
        <h3><?php echo HTML::anchor($ad->getUri(), $ad->getTitle(), array('title'=> $ad->getTitle()))?></h3>
        <div class="list_price"><?= $ad->getPrice( BoardConfig::instance()->priceTemplate($ad->price_unit) ) ?></div><br>
        <span class="quiet"><?php echo Model_BoardCategory::getField('name', $ad->category_id)?><br><b><?php echo Model_BoardCity::getField('name', $ad->city_id)?></b><br><?= Date::smart_date($ad->addtime)?> <?= date('G:i', $ad->addtime) ?> </span>
    </td>
    </tr>
    </table>
    </td></tr>
    <?endforeach;?>
    </table>
    <div class="clear"></div>
    <?php echo $pagination->render()?>
    <?else:?>
     <b>К сожалению не удалось найти объявления по указаным критериям</b><br /><br />

    <?endif?>
</div>
<div class="col_tools">
    <?if(!$search_by_user):?><?php echo Widget::factory('BoardTags') ?><?endif?>
    <div class="alcenter">
     <div class=g-plusone data-annotation=inline data-width=240 data-href="http://doreno.ru"></div>
                    <script>
                        window.___gcfg = {lang: 'ru'};
                        (function () {
                            var po = document.createElement('script');
                            po.type = 'text/javascript';
                            po.async = true;
                            po.src = 'https://apis.google.com/js/plusone.js';
                            var s = document.getElementsByTagName('script')[0];
                            s.parentNode.insertBefore(po, s);
                        })();
                    </script>
    <br /><br />
        <?php echo Widget::factory('Banner240x400')?>
    </div>
    <br>
    <div class="alcenter">
            <script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script><div class="yashare-auto-init" data-yashareL10n="ru" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,odnoklassniki,moimir,gplus" data-yashareTheme="counter"></div>
        </div>
        <div class="clear"></div>

</div>
<?if(!$search_by_user):?>
    <div class="clear"></div>
</div>
<div class="category_bg">
    <div class="container">
        <div class="category_holder">
            <br>
            <h2 class="small_12">Категории доски бесплатных объявлений <?php echo $subtitle?>:</h2>
            <?php echo Widget::factory('BoardSearchCategoryTree', array()) ?>
            <div class="clear"></div>
        </div>
    </div>
</div>
<div class="container">
<?endif?>