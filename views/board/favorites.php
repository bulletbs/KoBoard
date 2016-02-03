<?php defined('SYSPATH') OR die('No direct script access.');?>
<h1>Избранное</h1>

<?if(count($ads)):?>
    <table class="tableccat" id="adList">
        <?foreach($ads as $ad):?>
        <tr><td class="dashed">
            <table>
                <tr>
                    <td class="list_img"><?= HTML::anchor( $ad->getUri(), HTML::image(isset($photos[$ad->id]) ? $photos[$ad->id]->getThumbUri() : "/assets/board/css/images/noimage.png", array('alt'=>htmlspecialchars($ad->getTitle()), 'title'=>htmlspecialchars($ad->getTitle()))) . ($ad->photo_count ? '<span title="Всего фотографий: '.$ad->photo_count.'">'.$ad->photo_count.'</span>' : ''))?></td>
                    <td class="list_fav"><a href="#" class="ico_favorite" data-item="<?=$ad->id?>" title="Добавить в избранное"></a></td>
                    <td class="list_title">
                        <h3><?php echo HTML::anchor($ad->getUri(), $ad->title, array('title'=> $ad->title))?></h3>
                        <div class="list_price"><?= $ad->getPrice( BoardConfig::instance()->priceTemplate($ad->price_unit) ) ?></div><br>
                        <span class="quiet"><?php echo Model_BoardCategory::getField('name', $ad->category_id)?><br><b><?php echo Model_BoardCity::getField('name', $ad->city_id)?></b><br><?= Date::smart_date($ad->addtime)?> <?= date('G:i', $ad->addtime) ?> </span>
                    </td>
                    <td class="list_button"><?php echo HTML::anchor('#', '<i class=" fa fa-trash-o"></i> Удалить', array('class'=>'pure-button pure-button-error remove_favorite', 'data-id'=>$ad->id)) ?></td>
                </tr>
            </table>
        </td></tr>
        <?endforeach;?>
    </table>
    <?php echo $pagination->render()?>
<?else:?>
    <b>У вас нет объявлений в избранном</b>
<?endif?>