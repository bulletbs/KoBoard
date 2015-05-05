<?php defined('SYSPATH') OR die('No direct script access.');?>

<h1><?php echo $title ?></h1>
<div class="col_main">
    <div class="type_selector right">
        <?php echo HTML::anchor(Request::$current->uri() . URL::query(array('type'=>NULL)), __('Any'), array('class' => is_null(Arr::get($_GET, 'type')) ? 'active':''))?>
        <?php echo HTML::anchor(Request::$current->uri() . URL::query(array('type'=>Model_BoardAd::PRIVATE_TYPE)), __('Private'), array('class' => Arr::get($_GET, 'type')=== (string) Model_BoardAd::PRIVATE_TYPE ? 'active':''))?>
        <?php echo HTML::anchor(Request::$current->uri() . URL::query(array('type'=>Model_BoardAd::BUSINESS_TYPE)), __('Business'), array('class' => Arr::get($_GET, 'type')=== (string) Model_BoardAd::BUSINESS_TYPE? 'active':''))?>
    </div>
    <?if(isset($city_counter)):?>
        <div class="line"></div>
        <span class="showcity">
        <a href="#" id="showBigCity" class="active">крупные города</a>
        <a href="#" id="showAllCity">все</a>
        </span>
        <ul class="category_list" id="city_list"><?foreach($city_counter as $_cnt):?>
            <li <?php echo $_cnt['cnt']>100 ? 'class="bigcity"' : 'class="smallcity"'?>><?php echo HTML::anchor(Model_BoardCity::generateUri( Model_BoardCity::getField('alias', $_cnt['city_id'])), Model_BoardCity::getField('name', $_cnt['city_id']) . ' <span>' .$_cnt['cnt']. '</span>') ?></li>
        <?endforeach;?></ul>
        <div class="clear"></div>
    <?endif?>
    <?if(count($childs_categories)):?>
    <div class="line"></div>
    <ul class="category_list"><?foreach($childs_categories as $_cat):?>
    <li><?php echo HTML::anchor(Model_BoardCategory::generateUri($_cat->alias), $_cat->name) ?></li>
    <!--<li>--><?php //echo HTML::anchor($_cat->geturi(), $_cat->name) ?><!--</li>-->
    <?endforeach;?></ul>
    <div class="clear"></div>
    <?endif?>
    <?if(isset($main_filter)):?>
    <script type="text/javascript">
        var basecat_uri = '<?php echo $main_filter['base_uri'] ;?>';
        var subcat_options = <?php echo json_encode(array_flip($main_filter['aliases'])) ?>;
    </script>
    <div class="line"></div>
    <ul class="category_list"><?foreach($main_filter['options'] as $_catid=>$_cat):?>
    <li><?php echo HTML::anchor(Model_BoardFilter::generateUri(array_flip($main_filter['aliases'])[$_catid]), $_cat) ?></li>
    <?endforeach;?></ul>
    <div class="clear"></div>
    <?endif?>
    <div class="line"></div>


    <?if(count($ads)):?>
    <table class="tableccat" id="adList">
    <?foreach($ads as $ad):?>
    <tr><td class="dashed">
    <table>
    <tr>
    <td class="list_date"><?= Date::smart_date($ad->addtime)?><br><b><?= date('G:i', $ad->addtime) ?></b><a href="#" class="ico_favorite" data-item="<?=$ad->id?>" title="Добавить в избранное"></a></td>
    <td class="list_img"><?if(isset($photos[$ad->id])):?><img src="<?php echo $photos[$ad->id]->getThumbUri()?>"><?else:?><img alt=<?php echo $ad->title?>" src="/assets/board/css/images/noimage.png"/><?endif?></td>
    <td class="list_title"><h3><?php echo HTML::anchor($ad->getUri(), $ad->title, array('title'=> $ad->title))?></h3> <?php echo mb_substr($ad->description, 0, 150, 'UTF-8')?><br> <span class="quiet"><?php echo Model_BoardCity::getField('name', $ad->city_id)?><br><?php echo Model_BoardCategory::getField('name', $ad->category_id)?></span> </td>
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
    <!-- GOOGLE -->
    <script async="" src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <!-- Rokvel 240x400 -->
    <ins class="adsbygoogle" style="display:inline-block;width:240px;height:400px" data-ad-client="ca-pub-3458343082323839" data-ad-slot="7809786857"></ins>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
    <!-- END GOOGLE  -->
    <br>
    <div class="alcenter">
        <div class="proreklamu">
            <a href="http://www.proreklamu.com" target="_blank"><img src="http://zxcc.ru/images/logo_pr.png" alt="Портал про рекламу и маркетинг"></a>
        </div>
        <div class="alcenter">
            <script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>
            <div class="yashare-auto-init b-share_theme_counter" data-yasharel10n="ru" data-yasharequickservices="yaru,vkontakte,facebook,twitter,odnoklassniki,moimir" data-yasharetheme="counter" data-yasharelink="http://rokvel.ru/krasnoyarskiy_kray"><span class="b-share"><span class="b-share-btn__wrap"><a rel="nofollow" target="_blank" title="ВКонтакте" class="b-share__handle b-share__link b-share-btn__vkontakte" href="http://share.yandex.ru/go.xml?service=vkontakte&amp;url=http%3A%2F%2Frokvel.ru%2Fkrasnoyarskiy_kray&amp;title=%D0%9A%D1%80%D0%B0%D1%81%D0%BD%D0%BE%D1%8F%D1%80%D1%81%D0%BA%D0%B8%D0%B9%20%D0%BA%D1%80%D0%B0%D0%B9.%20%D0%94%D0%BE%D1%81%D0%BA%D0%B0%20%D0%B1%D0%B5%D1%81%D0%BF%D0%BB%D0%B0%D1%82%D0%BD%D1%8B%D1%85%20%D0%BE%D0%B1%D1%8A%D1%8F%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D0%B9%20ROKVEL.RU" data-service="vkontakte"><span class="b-share-icon b-share-icon_vkontakte"></span><span class="b-share-counter"></span></a></span><span class="b-share-btn__wrap"><a rel="nofollow" target="_blank" title="Facebook" class="b-share__handle b-share__link b-share-btn__facebook" href="http://share.yandex.ru/go.xml?service=facebook&amp;url=http%3A%2F%2Frokvel.ru%2Fkrasnoyarskiy_kray&amp;title=%D0%9A%D1%80%D0%B0%D1%81%D0%BD%D0%BE%D1%8F%D1%80%D1%81%D0%BA%D0%B8%D0%B9%20%D0%BA%D1%80%D0%B0%D0%B9.%20%D0%94%D0%BE%D1%81%D0%BA%D0%B0%20%D0%B1%D0%B5%D1%81%D0%BF%D0%BB%D0%B0%D1%82%D0%BD%D1%8B%D1%85%20%D0%BE%D0%B1%D1%8A%D1%8F%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D0%B9%20ROKVEL.RU" data-service="facebook"><span class="b-share-icon b-share-icon_facebook"></span><span class="b-share-counter">0</span></a></span><span class="b-share-btn__wrap"><a rel="nofollow" target="_blank" title="Twitter" class="b-share__handle b-share__link b-share-btn__twitter" href="http://share.yandex.ru/go.xml?service=twitter&amp;url=http%3A%2F%2Frokvel.ru%2Fkrasnoyarskiy_kray&amp;title=%D0%9A%D1%80%D0%B0%D1%81%D0%BD%D0%BE%D1%8F%D1%80%D1%81%D0%BA%D0%B8%D0%B9%20%D0%BA%D1%80%D0%B0%D0%B9.%20%D0%94%D0%BE%D1%81%D0%BA%D0%B0%20%D0%B1%D0%B5%D1%81%D0%BF%D0%BB%D0%B0%D1%82%D0%BD%D1%8B%D1%85%20%D0%BE%D0%B1%D1%8A%D1%8F%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D0%B9%20ROKVEL.RU" data-service="twitter"><span class="b-share-icon b-share-icon_twitter"></span><span class="b-share-counter">0</span></a></span><span class="b-share-btn__wrap"><a rel="nofollow" target="_blank" title="Одноклассники" class="b-share__handle b-share__link b-share-btn__odnoklassniki" href="http://share.yandex.ru/go.xml?service=odnoklassniki&amp;url=http%3A%2F%2Frokvel.ru%2Fkrasnoyarskiy_kray&amp;title=%D0%9A%D1%80%D0%B0%D1%81%D0%BD%D0%BE%D1%8F%D1%80%D1%81%D0%BA%D0%B8%D0%B9%20%D0%BA%D1%80%D0%B0%D0%B9.%20%D0%94%D0%BE%D1%81%D0%BA%D0%B0%20%D0%B1%D0%B5%D1%81%D0%BF%D0%BB%D0%B0%D1%82%D0%BD%D1%8B%D1%85%20%D0%BE%D0%B1%D1%8A%D1%8F%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D0%B9%20ROKVEL.RU" data-service="odnoklassniki"><span class="b-share-icon b-share-icon_odnoklassniki"></span><span class="b-share-counter">0</span></a></span><span class="b-share-btn__wrap"><a rel="nofollow" target="_blank" title="Мой Мир" class="b-share__handle b-share__link b-share-btn__moimir" href="http://share.yandex.ru/go.xml?service=moimir&amp;url=http%3A%2F%2Frokvel.ru%2Fkrasnoyarskiy_kray&amp;title=%D0%9A%D1%80%D0%B0%D1%81%D0%BD%D0%BE%D1%8F%D1%80%D1%81%D0%BA%D0%B8%D0%B9%20%D0%BA%D1%80%D0%B0%D0%B9.%20%D0%94%D0%BE%D1%81%D0%BA%D0%B0%20%D0%B1%D0%B5%D1%81%D0%BF%D0%BB%D0%B0%D1%82%D0%BD%D1%8B%D1%85%20%D0%BE%D0%B1%D1%8A%D1%8F%D0%B2%D0%BB%D0%B5%D0%BD%D0%B8%D0%B9%20ROKVEL.RU" data-service="moimir"><span class="b-share-icon b-share-icon_moimir"></span><span class="b-share-counter"></span></a></span><iframe style="display: none" src="//yastatic.net/share/ya-share-cnt.html?url=http%3A%2F%2Frokvel.ru%2Fkrasnoyarskiy_kray&amp;services=yaru,vkontakte,facebook,twitter,odnoklassniki,moimir"></iframe></span></div>
        </div>
        <div class="clear"></div>
    </div>
</div>