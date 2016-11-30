<?php defined('SYSPATH') OR die('No direct script access.');?>
<div itemscope itemtype="http://schema.org/Product">
    <div class="first message">
        <?php if(Auth::instance()->logged_in('admin')):?>
        <div class="right">
            <a href="<?php echo URL::site().Route::get('admin')->uri(array('controller'=>'board', 'id'=>$ad->id))?>?user_id=<?php echo $ad->user_id?>" class='pure-button' target="_blank" title="<?php echo __('All user ads')?>"><i class="fa fa-user"></i> <?php echo __('All user ads')?></a>
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
        <a href="" class="h1_favorite" id="go_favorite_2" data-item="<?php echo $ad->id?>" title="Удалить из избранного"></a>
        <h1 itemprop="name" class="big_36"><?php echo $ad->getTitle()?></h1>
        <div class="clear"></div>
        <div><b><?php echo __('Added')?>:</b> <?php echo Date::smart_date($ad->addtime) ?> в <?= date('G:i', $ad->addtime) ?>&nbsp; | &nbsp;<b><?php echo __('Views')?>:</b> <?php echo $ad->views ?>&nbsp; | &nbsp;<b>ID:</b> <?php echo $ad->id ?>&nbsp; | &nbsp;<a href="#advertisement">Текст объявления</a>&nbsp; | &nbsp;<a href="#characteristics">Характеристики</a>&nbsp; | &nbsp;<a href="#contacts">Контакты</a>&nbsp; | &nbsp;<a href="#price">Цена</a></div>
        <div class="line_nobg"></div>
        <div class="col_main">
        <?php echo Widget::factory('Banner728x90')?>
        <div class="line_nobg"></div>
        <?php if (count($photos) == 1): ?>
        <div class="showroom"><?php echo HTML::image($photos[0]->getPhotoUri(), array('class' => 'center', 'alt'=>$ad->getTitle(), 'title'=>$ad->getTitle(), 'itemprop'=>'image')) ?></div>
        <div class="line_nobg"></div>
        <?php elseif (count($photos) > 1): ?>
            <div id="showroom" class="showroom">
                <div class="clear"></div>
            </div>
            <div id="showstack" class="showstack"><?php foreach ($photos as $photo_id=>$photo): ?> <?php echo $photo->getPhotoTag($ad->getTitle(). ' - фотография #'.($photo_id+1), array('itemprop'=>'image')) ?> <?php endforeach ?></div>
            <div class="board_gallery">
                <ul id="thumbs" class="thumbs">
                    <?php foreach ($photos as $photo_id=>$photo): ?>
                        <li><?php echo HTML::anchor($photo->getPhotoUri(), $photo->getThumbTag($ad->getTitle(). ' - фотография #'.($photo_id+1))) ?></li>
                    <?php endforeach ?>
                </ul>
                <div class="clear"></div>
            </div>
                    <div class="line_nobg"></div>
        <?endif ?>
        <a name="advertisement"></a>
        <?php if(!empty($ad->description)):?>
            <div class="detail-desc" itemprop="description"><?php echo  nl2br($ad->description)?></div>
        <?php endif?>
        <br />

        <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
            <?php if(!$is_noprice_category):?>
                <div class="line_nobg"></div><a name="price"></a>
                <dl>
                    <dt><?php echo __( $is_job_category ? 'Salary' : 'Price' )?></dt>
                    <dd><span class="price" itemprop="price"><?php echo  $ad->getPrice($price_template) ?><meta itemprop="priceCurrency" content="<?php echo $ad->getPriceCurrencyISO();?>"><small><?php echo $ad->getTrade()?></small></span></dd>
                </dl>
            <?php endif?>
            <div class="line_nobg"></div><a name="characteristics"></a>
            <?php foreach($filters as $filter_id=>$filter): ?>
                <?php if(isset($filter['value']) && Model_BoardFiltervalue::haveValue($filter['value'])): ?>
                    <dl>
                        <dt><?php echo $filter['name'] ?></dt>
                        <dd><?php echo Model_BoardFiltervalue::echoFiltersValues($filter) ?></dd>
                    </dl>
                <?php endif; ?>
            <?php endforeach; ?>
            <dl>
                <dt><?php echo __('Ad type')?></dt>
                <dd><?php echo __( $is_job_category ? Model_BoardAd::$jobType[$ad->type] : Model_BoardAd::$adType[$ad->type]) ?></dd>
                <dt>Номер объявления: </dt>
                <dd> <?php echo $ad->id ?></dd>
            </dl>
            <div class="line_nobg"></div><a name="contacts"></a>
            <dl class="description" itemprop="seller" itemscope itemtype="http://schema.org/Person">
            <?if($ad->company_id && isset($company)):?>
                <dt>Магазин</dt>
                <dd><strong itemprop="name"><?php echo $company->name?></strong> <?php echo HTML::anchor($company->adsUri(), 'Показать все объявления магазина', array('target'=>'_blank', 'title'=>'Объявления магазина '.$company->name))?></dd>
                <dt>Контактное лицо</dt>
                <dd><strong><?php echo  $ad->name?></strong></dd>
            <?else:?>
                <dt>Автор</dt>
                <dd><strong itemprop="name"><?php echo  $ad->name?></strong> <?if($ad->user_id>0):?><a target="_blank" title="Объявления пользователя <?php echo $ad->name ?>" href="/all.html?userfrom=<?php echo $ad->id?>"> Найти все объявления пользователя</a><?endif?></dd>
            <?endif;?></dl>
            <div itemprop="availableAtOrFrom" itemscope itemtype="http://schema.org/Place">
                <meta itemprop="name" content="<?php echo $region->name.', '.$city->name?>">
                <dl itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                    <dt>Город</dt>
                    <dd><?php echo HTML::anchor($region->getUri(), $region->name, array('itemprop'=>'addressRegion')) ?>, <?php echo HTML::anchor($city->getUri(), $city->name, array('itemprop'=>'addressLocality')) ?></dd>-

                    <?if(!empty($ad->address)):?>
                        <dt>Адрес</dt>
                        <dd>
                        <span itemprop="streetAddress"><?php echo $ad->address?></span> &nbsp;&nbsp;<a href="#" data-address="<?php echo BoardConfig::instance()->country_name.', '.$region->name.', '. $city->name .', '.$ad->address?>" id="toggleMap">Показать на карте</a>
                        <div id="baloonHeader" class="hide"><?php echo $ad->getTitle() ?></div>
                        <div id="baloonContent" class="hide"><?php echo BoardConfig::instance()->country_name.', '.$region->name.', '. $city->name .', '.$ad->address?></div>
                        <div id="baloonFooter" class="hide"><b><?php echo __( $is_job_category ? 'Salary' : 'Price' )?>: <?php echo  $ad->getPrice($price_template) ?><small><?php echo $ad->getTrade()?></small></b></div>
                        <script src="http://api-maps.yandex.ru/2.0/?load=package.full&amp;lang=ru-RU"></script>
                        </dd>
                    <?endif?>
                </dl>
                <div class="showAddress" id="showAddress"></div>
                <meta name="format-detection" content="telephone=no">
                <dl>
                    <?if(!empty($ad->phone)):?>
                        <dt>Телефон</dt>
                        <dd><span id="hidden_contacts" itemprop="telephone">x (xxx) xxx xx xx  &nbsp;&nbsp;</span><a href='#' data-id='<?php echo  $ad->id?>' id='showContacts'>Показать телефон</a></dd>
                    <?endif?>
                    <?/*if(!empty($ad->email)):?>
                        <dt>E-mail</dt>
                        <dd><a id="sendMessage" data-id="<?php echo $ad->id?>" href="#"><?php echo __('Send message to user')?></a><!--noindex--><div id="mailto"></div><!--/noindex--></dd>
                    <?endif*/?>
                    <?if(!empty($ad->site)):?>
                        <dt>Сайт</dt>
                        <dd><a target="_blank" rel="nofollow" href="<?php echo $ad->getGotoLink()?>" itemprop="url"><?php echo $ad->site?></a></dd>
                    <?endif?>
                </dl>
            </div>
        </div>
        <!--noindex-->
        <div class="message_actions">
            <a rel="nofollow" href="#" id="sendMessage" class="action_mail" data-id="<?php echo $ad->id?>">Написать продавцу</a>
            <?if(isset($_COOKIE['board_favorites']) && isset($_COOKIE['board_favorites'][$ad->id])):?>
            <a rel="nofollow" href="#" class="action_favorite delfav" data-item="<?= $ad->id?>" id="go_favorite">Удалить из избранного</a>
            <?else:?>
            <a rel="nofollow" href="#" class="action_favorite" data-item="<?= $ad->id?>" id="go_favorite">В избранное</a>
            <?endif?>
            <a rel="nofollow" href="#" class="action_abuse" id="go_abuse">Пожаловаться</a>
            <a rel="nofollow" href="#" class="action_print" data-link="<?php echo $ad->getPrintLink()?>" id="go_print">Распечатать</a>
            <script type="text/javascript" src="//yastatic.net/es5-shims/0.0.2/es5-shims.min.js" charset="utf-8"></script>
            <script type="text/javascript" src="//yastatic.net/share2/share.js" charset="utf-8"></script>
            <div class="ya-share2" data-services="vkontakte,facebook,odnoklassniki,moimir,gplus,twitter" data-size="s"></div>
        </div>
        <div id="addabuse" class="hide">
            <form method="post" enctype="multipart/form-data" class="pure-form" id="abuseform" data-id="<?php echo $ad->id?>">
                <div class="line"></div>
                <?php echo Form::select('type', Model_BoardAbuse::$types, NULL, array('id'=>'abuseType'))?>
                <input type="submit" value="Отправить" class="pure-button">
                <div class="line"></div>
            </form>
        </div>
        <div id="mailto"></div>         <!--/noindex-->
        <?php echo Widget::factory('Banner728x90')?>
        <?if(isset($sim_ads)):?>
            <div class="line_nobg"></div>
            <div class="h2">Похожие объявления</div>
            <?foreach($sim_ads as $_ad):?>
                <div class="detail-also-item">
                <div class="detail-also-item-img"><?= HTML::anchor( $_ad->getUri(), HTML::image(isset($sim_ads_photos[$_ad->id]) ? $sim_ads_photos[$_ad->id]->getThumbUri() : "/assets/board/css/images/noimage.png", array('alt'=>htmlspecialchars($_ad->title))) )?></div>
                <h3><?php echo HTML::anchor($_ad->getUri(), $_ad->title, array('title'=> $_ad->title))?></h3>
                <div class="detail-also-item-price"><?= $_ad->getPrice( BoardConfig::instance()->priceTemplate($_ad->price_unit) ) ?></div>
                </div><?endforeach;?>
            <div class="line_nobg"></div>
        <?endif?>

       <?if(isset($user_ads)):?>
            <div class="line_nobg"></div>
            <h2>Другие объявления пользователя <?php echo $ad->name ?></h2>

            <table class="tableccat" id="adList">
            <?foreach($user_ads as $_ad):?>
                <tr><td class="dashed">
                    <table>
                        <tr>
                            <td class="list_img"><?= HTML::anchor( $_ad->getUri(), HTML::image(isset($user_ads_photos[$_ad->id]) ? $user_ads_photos[$_ad->id]->getThumbUri() : "/assets/board/css/images/noimage.png", array('alt'=>htmlspecialchars($_ad->title))) )?></td>
                            <td class="list_fav"><a href="#" class="ico_favorite" data-item="<?=$_ad->id?>" title="Добавить в избранное"></a></td>
                            <td class="list_title">
                                <h3><?php echo HTML::anchor($_ad->getUri(), $_ad->title, array('title'=> $_ad->title))?></h3>
                                <div class="list_price"><?= $_ad->getPrice( BoardConfig::instance()->priceTemplate($_ad->price_unit) ) ?></div><br>
                                <span class="quiet"><?php echo Model_BoardCategory::getField('name', $_ad->category_id)?><br><b><?php echo Model_BoardCity::getField('name', $_ad->city_id)?></b><br><?= Date::smart_date($_ad->addtime)?> <?= date('G:i', $_ad->addtime) ?> </span>
                            </td>
                        </tr>
                    </table>
                </td></tr>
            <?endforeach;?>
            </table>
            <div class="clear"></div>
            <br>
            <div class="alcenter">
                <h2>Показать <a target="_blank" title="Показать все объявления пользователя <?php echo $ad->name ?>" href="/all.html?userfrom=<?php echo $ad->id?>">все объявления пользователя</a></h2>
            </div>
            <br>
            <div class="clear"></div>
        <?endif?>
        </div>
    </div>
    <div class="col_tools">
        <?php echo Widget::factory('BoardTags', array('title'=>$ad->title.' '.$ad->description, 'pcategory_id'=>$ad->pcategory_id, 'category_id'=>$ad->category_id))?>
        <div class="alcenter">
            <?php echo Widget::factory('Banner240x400')?>
        </div>
    </div>

    <div class="line_nobg"></div>
    <h2>Смотрите также «<?php echo Model_BoardCategory::getField('name', $ad->category_id)?>» в Вашем регионе:</h2>
    <ul class="search_sub_col search_sub_col_4"><?$_step = ceil(count($region_cities_counts)/4); $_i=0;?>
        <?foreach($region_cities_counts as $_city_id=>$_city):?><?if($_i>0 && $_i%$_step==0):?></ul><ul class="search_sub_col search_sub_col_4"><?endif?>
        <li><?php echo HTML::anchor(Model_BoardCity::generateUri( Model_BoardCity::getField('alias', $_city_id)), Model_BoardCity::getField('name', $_city_id) . ' <span>' .$_city. '</span>') ?></li>
        <?$_i++?><?endforeach?>
    </ul>
</div>
<div class="clear"></div>

<?php echo $breadcrumbs->render()?>