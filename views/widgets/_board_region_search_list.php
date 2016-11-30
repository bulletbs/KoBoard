<div id="regionsList" class="pure-g selectorWrapper st-level">
<!--noindex-->
    <ul class="top_link"><li data-action="go" data-id="" data-alias="" data-title="Область"><i class="fa fa-search"></i> Искать по всем областям</li></ul>
    <h3>Области</h3>
    <div class="pure-u-1-5">
        <?$_i = 0;?>
        <ul>
        <?foreach($regions as $region):?>
        <?$_letter = mb_substr($region['name'],0,1);?>
        <?if($_i>1 && $_i>ceil(count($regions) / 5)):?>
        <?$_i = 0?>
        </ul>
    </div>
    <div class="pure-u-1-5">
        <ul><?endif;?><?$_i++?><li data-action="child" data-id="<?php echo $region['id']?>" data-alias="<?php echo $region['alias']?>" data-title="<?php echo $region['name']?>"><?if(!isset($_last_letter) || $_last_letter!=$_letter):?><span><?php echo $_letter?></span><?endif?><?php echo $region['name']?></li><?$_last_letter = $_letter;?><?endforeach?></ul>
    </div>
<!--/noindex-->
</div>