<!--noindex-->
<div id="categoriesSubcats_<?php echo $part['id']?>" class="pure-g selectorWrapper sub-level">
<ul class="top_link"><li data-action="go" data-alias="<?php echo $part['alias']?>" data-title="<?php echo $part['name']?>"><i class="fa fa-search"></i> Искать по всему разделу</li><li data-action="back"><i class="fa fa-arrow-left"></i> Вернуться к разделам</li></ul>
<h3><a href="<?php echo $part['link']?>"><?php echo $part['name']?></a></h3>
<div class="pure-u-1-3"><?$_c = 0;?><ul><?foreach($subcats as $subcat):?>
<?if($_c>1 && $_c>ceil(count($subcats) / 3)):?><?$_c = 0?></ul></div><div class="pure-u-1-3"><ul><?endif;?>
<?$_c++?><li data-action="go" data-id="<?php echo $subcat['id']?>" data-parent="<?php echo $subcat['parent_id']?>" data-alias="<?php echo $subcat['alias']?>" data-title="<?php echo $subcat['name']?>"><?php echo $subcat['name']?></li><?endforeach?></ul></div>
</div>
<!--/noindex-->