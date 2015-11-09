<?php defined('SYSPATH') OR die('No direct script access.');?>

<div class="col_main">
    <?php if(Auth::instance()->logged_in('admin')):?>
        <div class="right">
            <a href="<?php echo URL::site().Route::get('admin')->uri(array('controller'=>'board', 'action'=>'edit', 'id'=>$ad->id))?>" class='pure-button pure-button' target="_blank" title="<?php echo __('Edit')?>"><i class="fa fa-edit"></i> <?php echo __('Edit')?></a>
            <a href="<?php echo URL::site().Route::get('admin')->uri(array('controller'=>'board', 'action'=>'delete', 'id'=>$ad->id))?>" class='pure-button pure-button-error' target="_blank" title="<?php echo __('Delete')?>"><i class="fa fa-trash-o"></i> <?php echo __('Delete')?></a>
        </div>
        <script type="text/javascript">
            $('.pure-button-error').on('click', function(e){
                if(!confirm('Вы уверены?'))
                    e.preventDefault();
            });
        </script>
    <?php endif?>
<h1><?php echo $ad->getTitle() ?></h1>
<div class="clear"></div>

    <div class="first message">
        <div class="quiet"><?php echo __('Added')?> <?php echo Date::smart_date($ad->addtime) ?>&nbsp; | &nbsp;<?php echo __('Views')?>: <?php echo $ad->views ?></div>
        <div class="line"></div>
        <?php echo Widget::factory('Banner728x90')?>
        <?php if (count($photos) == 1): ?>
            <div class="showroom"><?php echo HTML::image($photos[0]->getPhotoUri(), array('class' => 'center', 'alt'=>$ad->getTitle(), 'title'=>$ad->getTitle())) ?></div>
        <?php elseif (count($photos) > 1): ?>
            <div id="showroom" class="showroom">
                <div class="clear"></div>
            </div>
            <div id="showstack" class="showstack"><?php foreach ($photos as $photo_id=>$photo): ?> <?php echo $photo->getPhotoTag($ad->getTitle(). ' - фотография #'.($photo_id+1)) ?> <?php endforeach ?></div>
            <div class="board_gallery">
                <ul id="thumbs" class="thumbs">
                    <?php foreach ($photos as $photo_id=>$photo): ?>
                        <li><?php echo HTML::anchor($photo->getPhotoUri(), $photo->getThumbTag($ad->getTitle(). ' - предпросмотр #'.($photo_id+1))) ?></li>
                    <?php endforeach ?>
                </ul>
                <div class="clear"></div>

            </div>
        <?endif ?>
        <div class="line"></div>
      <h2>Текст объявления:</h2>
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
        <?php if(!$is_noprice_category):?>
        <dl>
            <dt><?php echo __( $is_job_category ? 'Salary' : 'Price' )?></dt>
            <dd><span class="price"><?php echo $ad->getPrice($price_template)?><small><?php echo $ad->getTrade()?></small></span></dd>
        </dl>
        <?php endif?>
        <div class="line"></div>

        <dl class="description">
            <dt><?php echo __('Ad type')?></dt>
            <dd><?php echo __( $is_job_category ? Model_BoardAd::$jobType[$ad->type] : Model_BoardAd::$adType[$ad->type]) ?></dd>

            <dt>Автор</dt>
            <dd><strong><?php echo  $ad->name?></strong> <?if($ad->user_id>0):?><small class="quiet"><a href="/all.html?userfrom=<?php echo $ad->id?>">(<?php echo __('Search for more user ads') ?>)</a></small><?endif?></dd>

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

<br>
<script type="text/javascript" src="//yastatic.net/share/share.js" charset="utf-8"></script><div class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="small" data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir" data-yashareTheme="counter"></div>
    </div>
    <div class="line"></div>
    <?php echo Widget::factory('Banner728x90')?>
<?if(isset($user_ads)):?>
    <div class="line"></div>
    <h2>Другие объявления пользователя <?php echo $ad->name ?></h2>
    <?foreach($user_ads as $_ad):?>
    <div class="detail-also-item">
        <div class="detail-also-item-img"><?php echo HTML::anchor($_ad->getUri(), isset($user_ads_photos[$_ad->id]) ? $user_ads_photos[$_ad->id]->getThumbTag($_ad->getTitle()) : HTML::image('/assets/board/css/images/noimage.png'), array('title'=>$_ad->title))?></div>
        <h3><?php echo HTML::anchor($_ad->getUri(), $_ad->title)?></h3>
    </div>
    <?endforeach?>
    <div class="clear"></div>
<?endif?>
<?if(isset($sim_ads)):?>
    <div class="line"></div>
    <h2>Похожие объявления</h2>
    <table class="tableccat" id="adList">
    <?foreach($sim_ads as $_ad):?>
    <tr><td class="dashed">
        <table>
            <tr>
                <td class="list_date"><?= date("d.m.Y", $_ad->addtime)?><br><b><?= date('G:i', $_ad->addtime) ?></b><a href="#" class="ico_favorite" data-item="<?=$_ad->id?>" title="Добавить в избранное"></a></td>
                <td class="list_img"><?if(isset($sim_ads_photos[$_ad->id])):?><img src="<?php echo $sim_ads_photos[$_ad->id]->getThumbUri()?>"><?else:?><img alt=<?php echo $_ad->title?>" title=<?php echo $_ad->title?>" src="/assets/board/css/images/noimage.png"/><?endif?></td>
                <td class="list_title"><h3><?php echo HTML::anchor($_ad->getUri(), $_ad->title, array('title'=> $_ad->title))?></h3> <?php echo $_ad->getShortDescr() ?><br> <span class="quiet"><?php echo Model_BoardCity::getField('name', $_ad->city_id)?><br><?php echo Model_BoardCategory::getField('name', $_ad->category_id)?></span> </td>
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
    <ul class="message_icons" id="message_icons">
        <li> <?if(isset($_COOKIE['board_favorites']) && isset($_COOKIE['board_favorites'][$ad->id])):?> <a rel="nofollow" data-item="<?= $ad->id?>" href="#" id="ico_out_favorite">Удалить из избранного</a>
        <?else:?> <a rel="nofollow" href="#" data-item="<?= $ad->id ?>" id="ico_favorite">В избранное</a>
        <?endif?></li>
        <li><a rel="nofollow" href="#" data-link="<?php echo $ad->getPrintLink()?>" id="ico_print">Печать</a></li>
        <li><a rel="nofollow" href="/my-ads/edit/<?php echo $ad->id?>" id="ico_edit">Изменить</a></li>
        <li><a rel="nofollow" href="#" id="ico_note">Жалоба</a></li>
    </ul>
    <div class="clear"></div>
    <div id="addabuse" class="hide">
        <form method="post" enctype="multipart/form-data" class="pure-form" id="abuseform" data-id="<?php echo $ad->id?>">
            <div class="line"></div>
            <?php echo Form::select('type', Model_BoardAbuse::$types, NULL, array('id'=>'abuseType'))?>
            <input type="submit" value="Отправить" class="pure-button">
            <div class="line"></div>
        </form>
    </div>
    <div class="alcenter">
    <?php echo Widget::factory('Banner240x400')?>
    </div>
</div>