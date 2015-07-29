<?php defined('SYSPATH') OR die('No direct script access.');?>
<h1><?php echo __('Classifieds by category')?></h1>

<div class="region_tree">
<?foreach($parts as $part):?>
    <h2><?php echo HTML::anchor($part->getUri(), $part->name)?></h2>
    <?if(isset($categories[$part->id])):?><ul>
    <?foreach($categories[$part->id] as $category):?>
    <li><?php echo HTML::anchor($category->getUri(), $category->name)?></li>
    <?endforeach?></ul>
    <?endif?>
<?endforeach?></div>