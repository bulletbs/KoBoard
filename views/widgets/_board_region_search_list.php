<div id="regionsList" class="pure-g selectorWrapper st-level">
    <ul class="top_link"><li data-action="go" data-id="" data-alias="" data-title="Область">Искать по всем областям</li></ul>
    <h3>Области</h3>
    <div class="pure-u-1-4">
        <?$_i = 0;?>
        <ul>
        <?foreach($regions as $region):?>
        <?if($_i>1 && $_i>ceil(count($regions) / 4)):?>
        <?$_i = 0?>
        </ul>
    </div>
    <div class="pure-u-1-4">
        <ul><?endif;?><?$_i++?><li data-action="child" data-id="<?php echo $region['id']?>" data-alias="<?php echo $region['alias']?>" data-title="<?php echo $region['name']?>"><?php echo $region['name']?></li><?endforeach?></ul>
    </div>
</div>