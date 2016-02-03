<?php defined('SYSPATH') or die('No direct script access.');?>
<h1 class="uk-h2"><?php echo __('My ads') ?></h1>

<?= Flash::render('mobile/flash/flash') ?>

<a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'edit'))?>" class="uk-button uk-button-primary uk-float-right uk-margin-left"><?php echo __('Add ad') ?></a>
<?if(count($ads)):?><a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'refresh_all'))?>" class="uk-button uk-button uk-float-right"><i class="fa fa-refresh"></i> <?php echo __('Refresh All') ?></a><?endif?>
<div class="uk-clearfix"></div>

<?if(count($ads)):?>

<div class="uk-grid uk-margin-top uk-grid-width-small-1-1 uk-grid-width-medium-1-2 uk-grid-width-large-1-3">
<?foreach($ads as $ad):?>
    <div>
        <div class="uk-panel uk-panel-box uk-margin-bottom uk-text-center">
            <div class="uk-width-1-2 uk-float-left">
                <a href="<?php echo URL::site().$ad->getUri()?>"><img src="<?php echo isset($photos[$ad->id]) ? $photos[$ad->id]->getThumbUri() : "/assets/board/css/images/noimage.png"?>" class="uk-thumbnail"></a><br>
                <a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'edit', 'id'=>$ad->id))?>" class='uk-button' title="<?php echo __('Edit')?>"><i class="uk-icon-edit"></i></a>
                <a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'enable', 'id'=>$ad->id))?>" class='uk-button' title="<?php echo __('Status')?>"><i class="uk-icon-eye<?php echo !$ad->publish ? '-slash' : ''?>"></i></a>
                <?if($ad->isRefreshable()):?>
                    <a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'refresh', 'id'=>$ad->id))?>" class='uk-button' title="<?php echo __('Refresh')?>"><i class="uk-icon-refresh"></i></a>
                <?else:?>
                    <a class='uk-button uk-button uk-button-disabled' title="Обновление доступно один раз в 7 дней"><i class="uk-icon-refresh"></i></a>
                <?endif?>
                <a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'remove', 'id'=>$ad->id))?>" class='uk-button uk-button-danger' title="<?php echo __('Delete')?>"><i class="uk-icon-trash-o"></i></a>
            </div>
            <div class="uk-width-1-2 uk-float-left">
                <?php echo HTML::anchor($ad->getUri(), $ad->getTitle(), array('title'=> $ad->getTitle(), 'class'=>''))?>
                <p class="uk-text-bold"><?= $ad->getPrice( BoardConfig::instance()->priceTemplate($ad->price_unit) ) ?></p>
                <p class="uk-text-small uk-text-muted"><i class="uk-icon-calendar"></i> <?= Date::smart_date($ad->addtime)?> <?= date('G:i', $ad->addtime) ?>  &nbsp;/&nbsp;  <i class="uk-icon-eye"></i> <?php echo $ad->views ?></p>
            </div>
        </div>
    </div><?endforeach;?>
</div>
<?else:?>
<p class="uk-text-large">У Вас нет объявлений.<br>Чтоб создать новое объявлений нажмите кнопку "Добавить объявление"</p>
<?endif?>

<script type="text/javascript">
$('.uk-button-danger').on('click', function(e){
    if(!confirm('<?php echo __('Are you sure?')?>'))
        e.preventDefault();
});
</script>