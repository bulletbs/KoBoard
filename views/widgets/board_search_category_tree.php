<?php defined('SYSPATH') OR die('No direct script access.');?>
<ul class="category_menu"><?foreach($categories[0] as $_cat):?>
<li><?php echo HTML::anchor($_cat->getUri($city_alias), $_cat->name)?>
    <ul><?foreach($categories[$_cat->id] as $_subcat):?>
        <li><?php echo HTML::anchor($_subcat->getUri($city_alias), $_subcat->name)?></li><?endforeach?>
    </ul>
</li><?endforeach?>
</ul>