<!--noindex-->
<div id="regionsCities_<?php echo $region['id']?>" class="pure-g selectorWrapper sub-level">
<ul class="top_link"><li data-action="go" data-alias="<?php echo $region['alias']?>" data-title="<?php echo $region['name']?>"><i class="fa fa-search"></i> Искать по всему региону</li><li data-action="back"><i class="fa fa-arrow-left"></i> Вернуться к областям</li></ul>
<h3><?php echo $region['name']?></h3>
<div class="pure-u-1-5"><?$_c = 0;?><ul><?foreach($cities as $city):?><?$_letter = mb_substr($city['name'],0,1);?>
    <?if($_c>1 && $_c>ceil(count($cities) / 5)):?><?$_c = 0?></ul></div><div class="pure-u-1-5"><ul><?endif;?>
    <?$_c++?><li data-action="go" data-id="<?php echo $city['id']?>" data-alias="<?php echo $city['alias']?>">
    <?if(!isset($_last_letter) || $_last_letter!=$_letter):?><span><?php echo $_letter?></span><?endif?>
    <?php echo $city['name']?></li><?$_last_letter = $_letter?><?endforeach?>
    </ul></div>
</div>
<!--/noindex-->