<?php defined('SYSPATH') OR die('No direct script access.');?>

Родительский фильтр
<?php echo Form::select('parent_id', $parent_filters, $model->parent_id, array('id'=>"parentFilter"))?>

<div id="optionsList">
    <h3><?= __('Filter hints') ?></h3>
    <?foreach($parent_options as $parent_option_id=>$parent_option):?>
    <?php echo $parent_option ?><br>
    <?= Form::input('childhints['. $parent_option_id .']', $hints[$parent_option_id])?>
        <hr>
    <? endforeach ?>
</div>