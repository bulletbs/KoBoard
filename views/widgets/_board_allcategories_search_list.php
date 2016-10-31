<!--noindex-->
<div id="categoriesList" class="pure-g selectorWrapper st-level">
    <ul class="top_link"><li data-action="go" data-alias="" data-title="Категория">Искать по всем разделам</li></ul>
    <h3>Разделы</h3>
    <div class="pure-u-1-4">
        <?$_i = 0;?>
        <ul>
            <?foreach($categories as $category):?>
            <?if($_i>1 && $_i>ceil(count($categories) / 4)):?>
            <?$_i = 0?>
        </ul>
    </div>
    <div class="pure-u-1-4">
        <ul><?endif;?><?$_i++?><li data-action="children" data-id="<?php echo $category['id']?>" data-alias="<?php echo $category['alias']?>" data-title="<?php echo $category['name']?>"><?php echo $category['name']?></li><?endforeach?></ul>
    </div>
</div>
<?foreach($categories as $category):?><?if(isset($subcats[$category['id']])):?>
<div id="categoriesSubcats_<?php echo $category['id']?>" class="pure-g selectorWrapper sub-level">
<ul class="top_link"><li data-action="go" data-alias="<?php echo $category['alias']?>" data-title="<?php echo $category['name']?>">Искать по всему разделу</li><li data-action="back">Вернуться к разделам</li></ul>
<h3><a href="<?php echo $category['link']?>"><?php echo $category['name']?></a></h3>
<div class="pure-u-1-3"><?$_c = 0;?><ul><?foreach($subcats[$category['id']] as $subcat):?>
<?if($_c>1 && $_c>ceil(count($subcats[$category['id']]) / 3)):?><?$_c = 0?></ul></div><div class="pure-u-1-3"><ul><?endif;?>
<?$_c++?><li data-action="go" data-id="<?php echo $subcat['id']?>" data-parent="<?php echo $subcat['parent_id']?>" data-alias="<?php echo $subcat['alias']?>" data-title="<?php echo $subcat['name']?>"><?php echo $subcat['name']?></li><?endforeach?></ul></div>
</div>
<?endif?><?endforeach?>
<!--/noindex-->