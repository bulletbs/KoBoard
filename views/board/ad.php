<?php defined('SYSPATH') OR die('No direct script access.');?>

<h1><? echo $ad->title ?></h1>
<small><?php echo __('Added')?> <? echo Date::smart_date($ad->addtime) ?></small>
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

<div class="message">
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
        <dt><?php echo __('Price')?></dt>
        <dd><span class="price"><?php echo  $ad->price > 0? $ad->price.' '.$cfg['price_value']: __('negotiable')?></span></dd>
    </dl>
    <div class="line"></div>

    <dl class="description">
        <dt><?php echo __('Ad type')?></dt>
        <dd><?php echo __(Model_BoardAd::$adType[$ad->type]) ?></dd>

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
        <dd><?php echo  HTML::anchor($city->getUri(), $city->name) ?></dd>

        <?if(!empty($ad->email)):?>
            <dt>E-mail</dt>
            <dd><a id="sendMessage" data-id="<?php echo $ad->id?>" href="#"><?php echo __('Send message to user')?></a><!--noindex--><div id="mailto"></div><!--/noindex--></dd>
        <?endif?>

        <?if(!empty($ad->site)):?>
            <dt>Сайт</dt>
            <dd><a target="_blank" rel="nofollow" href="<?php echo $ad->getGotoLink()?>"><?php echo $ad->site?></a></dd>
        <?endif?>
    </dl>
</div>