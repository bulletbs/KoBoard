<?php defined('SYSPATH') OR die('No direct script access.');?>
<h1 class="uk-h2">Избранное</h1>

<?if(count($ads)):?>
    <div class="uk-grid uk-margin-top uk-grid-width-small-1-2 uk-grid-width-medium-1-4 uk-grid-width-large-1-5" id="adList">
    <?foreach($ads as $ad):?>
        <div>
            <div class="tm-favorite-block uk-margin-bottom uk-panel uk-panel-box uk-text-center">
                <a href="<?php echo URL::site().$ad->getUri()?>"><img src="<?php echo isset($photos[$ad->id]) ? $photos[$ad->id]->getThumbUri() : "/assets/board/css/images/noimage.png"?>" class="uk-thumbnail"></a>
                <div class="uk-align-center uk-margin-top"><?php echo HTML::anchor('#', '<i class="uk-icon-trash-o"></i> Удалить', array('class'=>'uk-button uk-button-danger remove_favorite', 'data-id'=>$ad->id)) ?></div>
                <div class="uk-text-small">
                    <?php echo HTML::anchor($ad->getUri(), $ad->getTitle(), array('title'=> $ad->getTitle(), 'class'=>''))?>
                    <div class="uk-text-bold"><?= $ad->getPrice( BoardConfig::instance()->priceTemplate($ad->price_unit) ) ?></div>
                </div>
            </div>
        </div>
    <?endforeach;?>
    </div>
    <?php echo $pagination->render()?>
<?else:?>
    <b>У вас нет объявлений в избранном</b>
<?endif?>