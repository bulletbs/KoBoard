<div id="regionsCities_<?php echo $region->id?>" class="pure-g selectorWrapper sub-level">
<ul class="top_link"><li data-action="go" data-alias="<?php echo $region->alias?>" data-title="<?php echo $region->name?>">Искать по всему региону</li><li data-action="back">Вернуться к областям</li></ul>
<h3><?php echo $region->name?></h3>
<div class="pure-u-1-5"><?$_c = 0;?><ul><?foreach($cities as $city):?>
<?if($_c>1 && $_c>ceil(count($cities) / 5)):?><?$_c = 0?></ul></div><div class="pure-u-1-5"><ul><?endif;?>
<?$_c++?><li data-action="go" data-id="<?php echo $city['id']?>" data-alias="<?php echo $city['alias']?>"><?php echo $city['name']?></li><?endforeach?></ul></div>
</div>