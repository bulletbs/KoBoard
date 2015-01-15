<?php defined('SYSPATH') OR die('No direct script access.');?>

<h1><?php echo $title ?></h1>


<?php echo Widget::load('BoardSearch', array(
    'region_name' => is_object($city) ? $city->name : NULL,
    'category_name' => is_object($category) ? $category->name : NULL,
));?>

<?if(count($childs_cities)):?>
    <div class="line"></div>
    <ul class="subcatList"><?foreach($childs_cities as $_alias => $_city):?>
        <li><?php echo HTML::anchor(Model_BoardCity::generateUri($_alias), $_city) ?></li>
    <?endforeach;?></ul>
<?endif?>
<?if(count($childs_categories)):?>
<div class="line"></div>
<ul class="subcatList"><?foreach($childs_categories as $_cat):?>
<li><?php echo HTML::anchor(Model_BoardCategory::generateUri($_cat->alias), $_cat->name) ?></li>
<!--<li>--><?php //echo HTML::anchor($_cat->geturi(), $_cat->name) ?><!--</li>-->
<?endforeach;?></ul>
<?endif?>
<div class="line"></div>


<?if(count($ads)):?>
<table class="tableccat" id="adList">
<?foreach($ads as $ad):?>
<tr><td class="dashed">
<table>
<tr>
<td class="list_date"><?= Date::smart_date($ad->addtime)?><br><b><?= date('G:i', $ad->addtime) ?></b><a href="#" class="ico_favorite" data-item="<?=$ad->id?>" title="Добавить в избранное"></a></td>
<td class="list_img"><?if(isset($photos[$ad->id])):?><img src="<?php echo $photos[$ad->id]->getThumbUri()?>"><?else:?><img alt=<?php echo $ad->title?>" src="/media/css/images/noimage_100.png"/><?endif?></td>
<td class="list_title"><h3><?php echo HTML::anchor($ad->getUri(), $ad->title, array('title'=> $ad->title))?></h3></td>
<td class="list_price"><?= ($ad->price > 0 ? $ad->price.' '.$cfg['price_value'] : '') ?></td>
</tr>
</table>
</td></tr>
<?endforeach;?>
</table>
<?php echo $pagination->render()?>
<?else:?>
 <b>К сожалению не удалось найти объявления по указаным критериям</b>
<?endif?>