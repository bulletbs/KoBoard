<?php defined('SYSPATH') or die('No direct script access.');?>

<div class="board_search_form pure-form">
<?php echo Form::open($form_action, array('id'=>'boardTopForm', 'method'=>'get'))?>
    <div id="queryInput" class="selector">
        <?php echo Form::input('query', Arr::get($_GET, 'query'), array('placeholder'=>'Найти по тексту', 'class'=>'query', 'id'=>'serchformQuery'))?>
    </div>
    <div id="regionLabel" class="selector">
        <?php echo Form::input(NULL, $region_name, array('placeholder'=>'Область', 'readonly'=>'readonly', 'id'=>'regionTopInput'))?>
        <?php echo $city_list?>
    </div>
    <div id="categoryLabel" class="selector selector-last">
        <?php echo Form::input(NULL, $category_name, array('placeholder'=>'Категория', 'readonly'=>'readonly', 'id'=>'categoryTopInput'))?>
        <?php echo $category_list?>
    </div>
    <div class="clear"></div>
    <div class="checkboxLabel"><?php echo Form::checkbox('wphoto', 1, Arr::get($_GET,'wphoto')>0)?> <label><?php echo __('with photo only')?></label></div>
    <div class="checkboxLabel"><?php echo Form::checkbox('wdesc', 1, Arr::get($_GET,'wdesc')>0)?> <label><?php echo __('search in title and description')?></label></div>
    <div class="clear"></div>
    <div id="filtersList" style="display: none;">
        <?php echo $filters?>
        <div class="filter">
<!--            <label>--><?php //echo __('Price')?><!--</label>-->
            <?= Form::input('price[from]', isset($price_filter['from']) ? $price_filter['from'] : NULL, array('id'=>'fromPriceFilter', 'placeholder'=>__('Price').' '.__('From'), 'autocomplete'=>'off')) ?>
            <?= Form::input('price[to]', isset($price_filter['to']) ? $price_filter['to'] : NULL, array('id'=>'toPriceFilter', 'placeholder'=>__('Price').' '.__('To'), 'autocomplete'=>'off')) ?>
        </div>
        <script type="text/javascript">
            $('#fromPriceFilter').TipComplete({
                values : [<?php echo $board_cfg['price_hints']?>],
                prefix: '<?php echo __('From')?>',
                suffix: '<?php echo $board_cfg['price_value']?>',
            });
            $('#toPriceFilter').TipComplete({
                values : [<?php echo $board_cfg['price_hints']?>],
                prefix: '<?php echo __('To')?>',
                suffix: '<?php echo $board_cfg['price_value']?>',
            });
        </script>
        <div class="clear"></div>
    </div>
    <?php echo Form::hidden(NULL, $category_alias, array('id'=>'categoryAlias'))?>
    <?php echo Form::hidden(NULL, $region_ailas, array('id'=>'regionAlias'))?>
    <?php echo Form::submit(NULL, 'Найти', array('id'=>'boardTopSubmit', 'class'=>'boardSubmit'))?>
    <div class="clear"></div>
<?php echo Form::close()?>
</div>