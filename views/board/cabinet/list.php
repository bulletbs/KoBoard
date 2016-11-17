<?php defined('SYSPATH') or die('No direct script access.');?>
<h1><?php echo __('My ads') ?></h1>

<?= Flash::render('global/flash') ?>

<a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'edit'))?>" class="pure-button pure-button-primary right"><?php echo __('Add ad') ?></a>
<?if(count($ads)):?><a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'refresh_all'))?>" class="pure-button pure-button right"><i class="fa fa-refresh"></i> <?php echo __('Refresh All') ?></a><?endif?>
<br class="clear">
<br class="clear">

<?if(count($ads)):?>
<table class="tableccat tableccat_full">
<?foreach($ads as $ad):?>
    <tr><td class="dashed">
        <table>
            <tr>
                <td class="list_img"><?= HTML::anchor( $ad->getUri(), HTML::image(isset($photos[$ad->id]) ? $photos[$ad->id]->getThumbUri() : "/assets/board/css/images/noimage.png", array('alt'=>htmlspecialchars($ad->getTitle()), 'title'=>htmlspecialchars($ad->getTitle()))) . ($ad->photo_count ? '<span title="Всего фотографий: '.$ad->photo_count.'">'.$ad->photo_count.'</span>' : ''))?></td>
                <td class="list_title">
                    <h3><?php echo HTML::anchor($ad->getUri(), $ad->title, array('title'=> $ad->title))?></h3>
                    <div class="list_price"><?= $ad->getPrice( BoardConfig::instance()->priceTemplate($ad->price_unit) ) ?></div><br>
                    <span class="quiet"><?php echo Model_BoardCategory::getField('name', $ad->category_id)?><br><b><?php echo Model_BoardCity::getField('name', $ad->city_id)?></b><br><?= Date::smart_date($ad->addtime)?> <?= date('G:i', $ad->addtime) ?> </span>
                </td>
                <td class="list_button">
                    <a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'edit', 'id'=>$ad->id))?>" class='pure-button pure-button' title="<?php echo __('Edit')?>"><i class="fa fa-edit"></i></a>
                    <a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'enable', 'id'=>$ad->id))?>" class='pure-button pure-button' title="<?php echo __('Status')?>"><i class="fa fa-eye<?php echo !$ad->publish ? '-slash' : ''?>"></i></a>
                    <?if($ad->isRefreshable()):?>
                    <a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'refresh', 'id'=>$ad->id))?>" class='pure-button pure-button' title="<?php echo __('Refresh')?>"><i class="fa fa-refresh"></i></a>
                    <?else:?>
                    <a class='pure-button pure-button pure-button-disabled' title="Обновление доступно один раз в 7 дней"><i class="fa fa-refresh"></i></a>
                    <?endif?>
                    <a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'remove', 'id'=>$ad->id))?>" class='pure-button pure-button-error' title="<?php echo __('Delete')?>"><i class="fa fa-trash-o"></i></a>
                    <br/><br/>
                    <span class="quiet">Просмотров за <?php echo Date::currentMonth()?>: <?php echo $ad->views ?></span>
                </td>
            </tr>
        </table>
    </td></tr>
<?endforeach;?>
</table>
<div class="clear"></div>
<?else:?>
<b>У Вас нет объявлений.<br>Чтоб создать новое объявлений нажмите кнопку "Добавить объявление"</b>
<?endif?>

<script type="text/javascript">
$('.pure-button-error').on('click', function(e){
    if(!confirm('<?php echo __('Are you sure?')?>'))
        e.preventDefault();
});
</script>