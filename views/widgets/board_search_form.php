<?php defined('SYSPATH') or die('No direct script access.');?>

<div class="board_search_form">
<?php echo Form::open($form_action, array('id'=>'boardTopForm'))?>
    <?php echo Form::input('query', Arr::get($_GET, 'query'), array('placeholder'=>'Найти по тексту', 'class'=>'query'))?>
    <div id="regionLabel" class="selector">
        <?php echo Form::input('region', $region_name, array('placeholder'=>'Область', 'readonly'=>'readonly', 'id'=>'regionTopInput'))?>
        <?php echo $city_list?>
    </div>
    <div id="categoryLabel" class="selector">
        <?php echo Form::input('region', $category_name, array('placeholder'=>'Категория', 'readonly'=>'readonly', 'id'=>'categoryTopInput'))?>
        <?php echo $category_list?>
    </div>
    <?php echo Form::submit('posted', 'Найти', array('id'=>'boardTopSubmit'))?>
    <?php echo Form::hidden('category_alias', $category_alias, array('id'=>'categoryAlias'))?>
    <?php echo Form::hidden('region_ailas', $region_ailas, array('id'=>'regionAlias'))?>
<?php echo Form::close()?>
</div>