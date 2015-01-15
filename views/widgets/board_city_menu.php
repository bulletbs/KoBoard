<?php defined('SYSPATH') or die('No direct script access.');?>

<div class="pure-menu pure-menu-open">
    <b class="pure-menu-heading">Области</b>
    <ul>
        <?foreach($cities as $city):?>
            <li><?php echo HTML::anchor($city->getUri(), $city->name)?></li>
<!--            <li>--><?php //echo HTML::anchor(Model_BoardCity::generateUri($city->alias), $city->name)?><!--</li>-->
        <?endforeach?>
    </ul>
</div>