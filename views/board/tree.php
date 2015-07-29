<?php defined('SYSPATH') OR die('No direct script access.');?>
<h1><?php echo __('Classifieds by region')?></h1>

<div class="region_tree">
<?foreach($regions as $region):?>
    <h2><?php echo HTML::anchor($region->getUri(), $region->name)?></h2>
    <?if(isset($cities[$region->id])):?><ul>
    <?foreach($cities[$region->id] as $city):?>
    <li><?php echo HTML::anchor($city->getUri(), $city->name)?></li>
    <?endforeach?></ul>
    <?endif?>
<?endforeach?></div>