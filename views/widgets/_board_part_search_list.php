<!--noindex-->
<div id="categoriesList" class="pure-g selectorWrapper st-level">
    <ul class="top_link"><li data-action="go" data-alias="" data-title="Категория"><i class="fa fa-search"></i> Искать по всем разделам</li></ul>
    <h3>Разделы</h3>
    <div class="pure-u-1-4">
        <?$_i = 0;?>
        <ul>
            <?foreach($parts as $part):?>
            <?if($_i>1 && $_i>ceil(count($parts) / 4)):?>
            <?$_i = 0?>
        </ul>
    </div>
    <div class="pure-u-1-4">
        <ul><?endif;?><?$_i++?><li data-action="children" data-id="<?php echo $part['id']?>" data-alias="<?php echo $part['alias']?>" data-title="<?php echo $part['name']?>"><?php echo $part['name']?></li><?endforeach?></ul>
    </div>
</div>
<!--/noindex-->