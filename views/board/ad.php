<?php defined('SYSPATH') OR die('No direct script access.');?>

<h1><? echo $ad->title ?></h1>
<small><?php echo __('Added')?> <? echo Date::smart_date($ad->addtime) ?></small>
<div class="clear"></div>

<div class="col_main">
    <div class="first message">

        <? if (count($photos) == 1): ?>
            <div class="showroom"><? echo HTML::image($photos[0]->getPhotoUri(), array('class' => 'center')) ?></div>
        <? elseif (count($photos) > 1): ?>
            <div id="showroom" class="showroom">
                <div class="clear"></div>
            </div>
            <div id="showstack" class="showstack"><? foreach ($photos as $photo): ?> <? echo $photo->getPhotoTag() ?> <? endforeach ?></div>
            <div class="board_gallery">
                <ul id="thumbs" class="thumbs">
                    <? foreach ($photos as $photo): ?>
                        <li><? echo HTML::anchor($photo->getPhotoUri(), $photo->getThumbTag()) ?></li>
                    <? endforeach ?>
                </ul>
                <div class="clear"></div>

            </div>
        <?endif ?>

        <div class="line"></div>
        <?php if(!empty($ad->description)):?>
            <div class="detail-desc"><?php echo  nl2br($ad->description)?></div>
            <div class="line"></div>
        <?php endif?>
        <?php foreach($filters as $filter_id=>$filter): ?>
            <?php if(isset($filter['value']) && Model_BoardFiltervalue::haveValue($filter['value'])): ?>
                <dl>
                    <dt><?php echo $filter['name'] ?></dt>
                    <dd><?php echo Model_BoardFiltervalue::echoFiltersValues($filter) ?></dd>
                </dl>
            <?php endif; ?>
        <?php endforeach; ?>
        <dl>
            <dt><?php echo __( $is_job_category ? 'Salary' : 'Price' )?></dt>
            <dd><span class="price"><?php echo  $ad->price > 0? $ad->price.' '.$board_config['price_value']: __('negotiable')?></span></dd>
        </dl>
        <div class="line"></div>

        <dl class="description">
            <dt><?php echo __('Ad type')?></dt>
            <dd><?php echo __( $is_job_category ? Model_BoardAd::$jobType[$ad->type] : Model_BoardAd::$adType[$ad->type]) ?></dd>

            <dt>Автор</dt>
            <dd><strong><?php echo  $ad->name?></strong> <?if($ad->user_id>0):?><small class="quiet"><a href="#">(<?php echo __('Search for more user ads') ?>)</a></small><?endif?></dd>

            <?if(!empty($ad->address)):?>
            <dt>Адрес</dt>
            <dd>
                <?php echo $ad->address?> &nbsp;&nbsp;<a href="#" rel="<?php echo  $city->name .', '.$ad->address?>" id="toggleMap">Показать на карте</a>
                <script language="javascript" src="http://api-maps.yandex.ru/2.0/?load=package.full&amp;lang=ru-RU"></script>
            </dd>
        </dl>
        <div class="showAddress" id="showAddress"></div>
        <dl>
            <?endif?>

            <?if(!empty($ad->phone)):?>
                <dt>Телефон</dt>
                <dd><span id="hidden_contacts">x (xxx) xxx xx xx  &nbsp;&nbsp;</span><a href='#' data-id='<?php echo  $ad->id?>' id='showContacts'>Показать телефон</a></dd>
            <?endif?>

            <dt>Город</dt>
            <dd><?php echo HTML::anchor($city->getUri(), $city->name) ?></dd>

            <?if(!empty($ad->email)):?>
                <dt>E-mail</dt>
                <dd><a id="sendMessage" data-id="<?php echo $ad->id?>" href="#"><?php echo __('Send message to user')?></a><!--noindex--><div id="mailto"></div><!--/noindex--></dd>
            <?endif?>

            <?if(!empty($ad->site)):?>
                <dt>Сайт</dt>
                <dd><a target="_blank" rel="nofollow" href="<?php echo $ad->getGotoLink()?>"><?php echo $ad->site?></a></dd>
            <?endif?>
        </dl>
        <div class="quiet"><br>Номер объявления: <?php echo $ad->id ?></div>
        <script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>
        <br>
        <div class="note-block">Чтобы откликнуться на <b><?php echo $ad->title?></b>, Вы можете отправить сообщение поставщику <b><?php echo $ad->name?>, город <?php echo $city->name?></b>, либо связаться с ним одним из указанных выше способов.<br>Пожалуйста упомяните при разговоре что Вы нашли это объявление на сайте объявлений <?php echo $config['project']['name']?></div>
    </div>
<?if(isset($user_ads)):?>
    <div class="line"></div>
    <h2>Другие объявления пользователя <?php echo $ad->name ?></h2>
    <?foreach($user_ads as $_ad):?>
    <div class="detail-also-item">
        <div class="detail-also-item-img"><?php echo HTML::anchor($_ad->getUri(), isset($user_ads_photos[$_ad->id]) ? $user_ads_photos[$_ad->id]->getThumbTag() : HTML::image('/assets/board/css/images/noimage.png'), array('title'=>$_ad->title))?></div>
        <h3><?php echo HTML::anchor($_ad->getUri(), $_ad->title)?></h3>
    </div>
    <?endforeach?>
    <div class="clear"></div>
<?endif?>
<?if(isset($sim_ads)):?>
    <div class="line"></div>
    <h2>Похожие объявления</h2>
    <table class="tableccat" id="adList">
    <?foreach($sim_ads as $ad):?>
    <tr><td class="dashed">
        <table>
            <tr>
                <td class="list_date"><?= date("d.m.Y", $ad->addtime)?><br><b><?= date('G:i', $ad->addtime) ?></b><a href="#" class="ico_favorite" data-item="<?=$ad->id?>" title="Добавить в избранное"></a></td>
                <td class="list_img"><?if(isset($sim_ads_photos[$ad->id])):?><img src="<?php echo $sim_ads_photos[$ad->id]->getThumbUri()?>"><?else:?><img alt=<?php echo $ad->title?>" src="/assets/board/css/images/noimage.png"/><?endif?></td>
                <td class="list_title"><h3><?php echo HTML::anchor($ad->getUri(), $ad->title, array('title'=> $ad->title))?></h3> <?php echo mb_substr($ad->description, 0, 150, 'UTF-8')?><br> <span class="quiet"><?php echo Model_BoardCity::getField('name', $ad->city_id)?><br><?php echo Model_BoardCategory::getField('name', $ad->category_id)?></span> </td>
            </tr>
        </table>
    </td></tr>
    <?endforeach;?>
    </table>
<?endif?>

<div class="line"></div>
<h2>Смотрите также разделы портала:</h2>
<a href="/all/">Бесплатные объявления в России</a> <?foreach($category_parents as $_parent) echo ' / '.HTML::anchor($_parent->getUri(), $_parent->name.' в России')?><br>
<?foreach($city_parents as $_city):?>
    <?php echo HTML::anchor($_city->getUri(), 'Бесплатные объявления в '.$_city->name_in)?> <?foreach($category_parents as $_parent) echo ' / '.HTML::anchor(Model_BoardCity::generateUri($_city->alias, $_parent->alias), $_parent->name.' в '.$_city->name_in)?><br>
<?endforeach?>

<div class="line"></div>
<h2>Смотрите также «<?php echo Model_BoardCategory::getField('name', $ad->category_id)?>»:</h2>
<ul class="seeAlso"><?foreach($region_cities_ids as $_id):?>
<li><?php echo HTML::anchor(Model_BoardCity::generateUri( Model_BoardCity::getField('alias', $_id), Model_BoardCategory::getField('alias', $ad->category_id) ), Model_BoardCity::getField('name', $_id))?></li>
<?endforeach?></ul>

</div>
<div class="col_tools">
    <div class="last">
        <ul class="message_icons" id="message_icons">
            <li> <?if(isset($_COOKIE['board_favorites']) && isset($_COOKIE['board_favorites'][$ad->id])):?> <a rel="nofollow" data-item="<?= $ad->id?>" href="#" id="ico_out_favorite">Удалить из избранного</a>
            <?else:?> <a rel="nofollow" href="#" data-item="<?= $ad->id ?>" id="ico_favorite">В избранное</a>
            <?endif?></li>
            <li><a rel="nofollow" href="#" data-link="<?php echo $ad->getPrintLink()?>" id="ico_print">Печать</a></li>
            <li><a rel="nofollow" href="/profile/board" id="ico_edit">Изменить</a></li>
            <li><a rel="nofollow" href="#" id="ico_note">Жалоба</a></li>
        </ul>
        <div class="clear"></div>
        <div id="addabuse" class="hide"><br>
            <form method="post" enctype="multipart/form-data" class="pure-form" id="abuseform" data-id="<?php echo $ad->id?>">
                <div class="line"></div>
                <?php echo Form::select('type', Model_BoardAbuse::$types, NULL, array('id'=>'abuseType'))?>
                <input type="submit" value="Отправить" class="pure-button">
            </form>
        </div>
    </div>
</div>