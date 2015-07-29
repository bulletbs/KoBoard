<?php defined('SYSPATH') OR die('No direct script access.');?>



<div class="pure-g categoryList">
    <div class="pure-u-1-3">
        <?$_i = 0;?>
<?foreach($categories[0] as $category):?>
    <?if($_i>1 && $_i>ceil($categories_count / 3)):?>
    <?$_i = 0?>
    </div>
    <div class="pure-u-1-3">
    <?endif;?><?$_i++?><?//=$_i?>
    <h3><?php echo HTML::anchor($category->getUri(), $category->name)?></h3>
    <ul>
    <?foreach($categories[$category->id] as $subcategory):?><?$_i++?><?//=$_i?>
    <li><?php echo HTML::anchor($subcategory->getUri(), $subcategory->name)?></li><?endforeach?>
    </ul>
<?endforeach?>
    </div>
</div>
