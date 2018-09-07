<?php defined('SYSPATH') or die('No direct script access.');?>
<div class="board_search_form">
<?php echo Form::open($form_action, array('id'=>'boardTopForm', 'method'=>'get'))?>
    <div id="queryInput" class="selector">
        <?php echo Form::input('query', Arr::get($_GET, 'query'), array('placeholder'=>'Поиск по объявлениям', 'class'=>'query', 'id'=>'serchformQuery'))?>
    </div>
    <div id="regionLabel" class="selector">
        <?php echo Form::input(NULL, $region_name, array('placeholder'=>'Область', 'readonly'=>'readonly', 'id'=>'regionTopInput'))?>
    </div>
    <div id="categoryLabel" class="selector selector-last">
        <?php echo Form::input(NULL, $category_name, array('placeholder'=>'Категория', 'readonly'=>'readonly', 'id'=>'categoryTopInput'))?>
    </div>
    <?php echo Form::submit(NULL, 'Найти', array('id'=>'boardTopSubmit', 'class'=>'boardSubmit'))?>
    <div class="clear"></div>
    <div class="checkboxLabel"><?php echo Form::checkbox('wphoto', 1, Arr::get($_GET,'wphoto')>0)?> <label><?php echo __('with photo only')?></label></div>
    <div class="checkboxLabel"><?php echo Form::checkbox('wdesc', 1, Arr::get($_GET,'wdesc')>0)?> <label><?php echo __('search in title and description')?></label></div>

    <div class="clear"></div>
    <?php echo Form::hidden(NULL, $category_alias, array('id'=>'categoryAlias'))?>
    <?php echo Form::hidden(NULL, $region_ailas, array('id'=>'regionAlias'))?>
    <div class="clear"></div>
<?php echo Form::close()?>
</div>
<script type="text/javascript">
    var basecat_uri = '<?php echo $base_uri ;?>';
    var subcat_options = <?php echo json_encode(array_flip($subcat_options)) ?>;
    <?if(!is_null($subcat_selected)):?>var subcat_selected = <?php echo $subcat_selected?>;<?endif?>
</script>