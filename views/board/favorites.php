<?php defined('SYSPATH') OR die('No direct script access.');?>
<h1>Избранное</h1>

<?if(count($ads)):?>
    <table class="tableccat" id="adList">
        <?foreach($ads as $ad):?>
        <tr><td class="dashed">
            <table>
                <tr>
                    <td class="list_date"><?= date("d.m.Y", $ad->addtime)?><br><b><?= date('G:i', $ad->addtime) ?></b><a href="#" class="ico_favorite" data-item="<?=$ad->id?>" title="Добавить в избранное"></a></td>
                    <td class="list_img"><?if(isset($photos[$ad->id])):?><img src="<?php echo $photos[$ad->id]->getThumbUri()?>"><?else:?><img alt=<?php echo $ad->title?>" src="/assets/board/css/images/noimage.png"/><?endif?></td>
                    <td class="list_title"><h3><?php echo HTML::anchor($ad->getUri(), $ad->title, array('title'=> $ad->title))?></h3></td>
                    <td class="list_price"><?= $ad->getPrice($board_config['price_units']['templates'][ $ad->price_unit ]) ?></td>
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