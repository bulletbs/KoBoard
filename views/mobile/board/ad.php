<?php defined('SYSPATH') OR die('No direct script access.');?>
    <article class="uk-article">
        <h1 class="uk-h2"><?php echo $ad->getTitle()?> в <?php echo $city->name_in?></h1>
        <p class="uk-article-meta"><?php echo __('Added')?>: <?php echo Date::smart_date($ad->addtime) ?>&nbsp; | &nbsp;<?php echo __('Views')?>: <?php echo $ad->views ?></p>

        <?php if (count($photos) == 1): ?>
            <?php echo HTML::image($photos[0]->getPhotoUri(), array('class' => 'center', 'alt'=>$ad->getTitle(), 'title'=>$ad->getTitle())) ?>
        <?php elseif (count($photos) > 1): ?>
            <ul class="tm-thumbnav-photos uk-thumbnav">
            <?php foreach ($photos as $photo_id=>$photo): ?>
                <li><a href="<?php echo $photo->getPhotoUri()?>" data-uk-lightbox="{group:'item_gallery'}"><?php echo HTML::image($photo->getThumbUri(), array('class'=>'uk-border-rounded', 'width'=>'200', 'title'=>$ad->getTitle(). ' - фотография #'.($photo_id+1), 'alt'=>$ad->getTitle(). ' - фотография #'.($photo_id+1))) ?></a></li>
            <?php endforeach ?>
            </ul>
        <?endif ?>

        <?php if(!empty($ad->description)):?>
            <div class="uk-panel uk-panel-box uk-margin-top"><?php echo  nl2br($ad->description)?></div>
        <?php endif?>

        <?php if(!$is_noprice_category):?>
            <dl class="uk-description-list-line">
                <dt><?php echo __( $is_job_category ? 'Salary' : 'Price' )?></dt>
                <dd><span class="price"><?php echo  $ad->getPrice($price_template) ?><meta content="<?php echo $ad->getPriceCurrencyISO();?>"><small><?php echo $ad->getTrade()?></small></span></dd>
        <?php endif?>
        <?php foreach($filters as $filter_id=>$filter): ?>
            <?php if(isset($filter['value']) && Model_BoardFiltervalue::haveValue($filter['value'])): ?>
                <dt><?php echo $filter['name'] ?></dt>
                <dd><?php echo Model_BoardFiltervalue::echoFiltersValues($filter) ?></dd>
            <?php endif; ?>
        <?php endforeach; ?>
            <dt><?php echo __('Ad type')?></dt>
            <dd><?php echo __( $is_job_category ? Model_BoardAd::$jobType[$ad->type] : Model_BoardAd::$adType[$ad->type]) ?></dd>
            <dt>Номер объявления: </dt>
            <dd> <?php echo $ad->id ?></dd>
            <dt>Автор</dt>
            <dd><strong><?php echo  $ad->name?></strong> <?if($ad->user_id>0):?><?endif?></dd>
            <dt>Город</dt>
            <dd><?php echo HTML::anchor($region->getUri(), $region->name) ?>, <?php echo HTML::anchor($city->getUri(), $city->name, array('itemprop'=>'addressLocality')) ?></dd>
        <?if(!empty($ad->address)):?>
            <dt>Адрес</dt>
            <dd>
                <?php echo $ad->address?>
<!--                &nbsp;&nbsp;-->
<!--                <a href="#" rel="--><?php //echo BoardConfig::instance()->country_name.', '. $city->name .', '.$ad->address?><!--" id="toggleMap">Показать на карте</a>-->
                <script language="javascript" src="http://api-maps.yandex.ru/2.0/?load=package.full&amp;lang=ru-RU"></script>
            </dd>
        <?endif?>
        <?if(!empty($ad->phone)):?>
            <dt>Телефон</dt>
            <dd><span id="hidden_contacts">x (xxx) xxx xx xx  &nbsp;&nbsp;</span><a href='#' data-id='<?php echo  $ad->id?>' id='showContacts'>Показать телефон</a></dd>
        <?endif?>
        <?if(!empty($ad->site)):?>
            <dt>Сайт</dt>
            <dd><a target="_blank" rel="nofollow" href="<?php echo $ad->getGotoLink()?>"><?php echo $ad->site?></a></dd>
        <?endif?>
        </dl>
        <div class="showAddress" id="showAddress"></div>

        <div class="message_actions uk-border-rounded">
            <div class="uk-float-right">
                <script type="text/javascript" src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js" charset="utf-8"></script>
                <script type="text/javascript" src="//yastatic.net/share2/share.js" charset="utf-8"></script>
                <div class="ya-share2" data-services="vkontakte,facebook,odnoklassniki,moimir,gplus,twitter" data-size="s"></div>
            </div>
            <a rel="nofollow" href="#" id="sendMessage" class="uk-text-nowrap" data-id="<?php echo $ad->id?>"><i class="uk-icon-paper-plane"></i> Написать продавцу</a>&nbsp;&nbsp;
            <?if(isset($_COOKIE['board_favorites']) && isset($_COOKIE['board_favorites'][$ad->id])):?>
                <a rel="nofollow" href="#" class="uk-text-nowrap delfav" data-item="<?= $ad->id?>" id="go_favorite"><i class="uk-icon-star"></i> Удалить из избранного</a>
            <?else:?>
                <a rel="nofollow" href="#" class="uk-text-nowrap" data-item="<?= $ad->id?>" id="go_favorite"><i class="uk-icon-star"></i> В избранное</a>
            <?endif?>&nbsp;&nbsp;
            <a rel="nofollow" href="#" class="uk-text-nowrap" id="go_abuse"><i class="uk-icon-thumbs-down"></i> Пожаловаться</a>
        </div>
    <!--noindex--><div class="uk-panel-box" id="addabuse" style="display: none;">
            <div class="uk-text-bold">Укажите причину жалобы:</div>
            <form method="post" enctype="multipart/form-data" class="uk-form" id="abuseform" data-id="<?php echo $ad->id?>">
                <div class="line"></div>
                <?php echo Form::select('type', Model_BoardAbuse::$types, NULL, array('id'=>'abuseType'))?>
                <input type="submit" value="Отправить" class="uk-button">
                <div class="line"></div>
            </form>
        </div>
        <div id="mailto"></div><!--/noindex-->

    </article>