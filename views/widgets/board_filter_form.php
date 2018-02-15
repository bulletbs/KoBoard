<?php defined('SYSPATH') or die('No direct script access.');?>
<div class="board_search_form board_filter_form">
<?php echo Form::open($form_action, array('id'=>'boardTopForm', 'method'=>'get'))?>

	<?if(count($filters)):?>
		<?foreach($filters as $filter_id=>$data):?>
            <div class="filter">
                <label><?php echo $data['name']?></label>
				<? if($data['type'] == 'select' && isset($data['main'])): ?>
					<?$params = array('data-id'=>$filter_id);?>
					<?if(isset($data['main']) && $data['main'] > 0) $params['data-main'] = 1;?>
					<?= Form::select('filters['.$filter_id.']', isset($data['options']) ? $data['options'] : array(), isset($data['value']) ? $data['value'] : NULL, $params) ?>
				<? elseif($data['type'] == 'childlist'): ?>
					<?= Form::select('filters['.$filter_id.']', isset($data['options']) ? $data['options'] : array(), isset($data['value']) ? $data['value'] : NULL, Arr::merge(array('data-id'=>$filter_id, 'data-parent'=>$data['parent']), !isset($data['options']) || !count($data['options']) ? array('disabled'=>'disabled') : array()) ) ?>
				<? elseif($data['type'] == 'select'): ?>
					<?= Form::select('filters['.$filter_id.'][]', isset($data['options']) ? $data['options'] : array(), isset($data['value']) ? $data['value'] : NULL, array('id'=>'searchFilter'.$filter_id, 'data-id'=>$filter_id, 'multiple'=>'multiple', 'data-label'=>$data['name'])) ?>
                    <script type="text/javascript">
                        $(function() { $('#<?php echo 'searchFilter'.$filter_id?>').multipleSelect({
                            selectAllText: '<?php echo __('Any')?>',
                            allSelected: '<?php echo __('Any')?>',
                            countSelected: '# из %',
                            selectAll: false,
                            placeholder: ''
                        }); });
                    </script>
				<? elseif($data['type'] == 'text'): ?>
					<?= Form::input('filters['.$filter_id.']', isset($data['value']) ? $data['value'] : NULL) ?>
				<? elseif($data['type'] == 'digit' || $data['type'] == 'childnum'): ?>
				<?= Form::input('filters['.$filter_id.'][from]', isset($data['value']['from']) ? $data['value']['from'] : NULL, array('id'=>'fromFilter'.$filter_id, 'placeholder'=>__('From'), 'autocomplete'=>'off', 'class'=>'fromto')) ?>
				<?= Form::input('filters['.$filter_id.'][to]', isset($data['value']['to']) ? $data['value']['to'] : NULL, array('id'=>'toFilter'.$filter_id, 'placeholder'=>__('To'), 'autocomplete'=>'off', 'class'=>'fromto')) ?>
                    <script type="text/javascript">
                        $('#fromFilter<?php echo $filter_id?>').TipComplete({
                            values : [<?php echo $data['hints'] ?>],
                            prefix: '<?php echo __('From')?>',
                            suffix: '<?php echo $data['units']?>',
                            no_digits: '<?php echo $data['no_digits']?>'
                        });
                        $('#toFilter<?php echo $filter_id?>').TipComplete({
                            values : [<?php echo $data['hints'] ?>],
                            prefix: '<?php echo __('To')?>',
                            suffix: '<?php echo $data['units']?>',
                            no_digits: '<?php echo $data['no_digits']?>'
                        });
                    </script>
				<? elseif($data['type'] == 'checkbox'): ?>
					<?= Form::checkbox('filters['.$filter_id.']', 1, isset($data['value']) && $data['value'] ? TRUE : FALSE) ?>
				<? elseif($data['type'] == 'optlist'): ?>
				<?= Form::select('filters['.$filter_id.'][]', isset($data['options']) ? array_values($data['options']) : array(), isset($data['value']) ? $data['value'] : NULL, array('id'=>'searchFilter'.$filter_id, 'data-id'=>$filter_id, 'multiple'=>'multiple')) ?>
                    <script type="text/javascript">
                        $(function() { $('#<?php echo 'searchFilter'.$filter_id?>').multipleSelect(multiselect_options); });
                    </script>
				<?endif;?>
            </div>
		<?endforeach;?>
	<?endif;?>
	<?if($priced_category):?>
        <div class="filter">
			<?= Form::input('price[from]', isset($price_filter['from']) ? $price_filter['from'] : NULL, array('id'=>'fromPriceFilter', 'placeholder'=>__( $is_job_category ? 'Salary' : 'Price').' '.__('From'), 'autocomplete'=>'off')) ?>
			<?= Form::input('price[to]', isset($price_filter['to']) ? $price_filter['to'] : NULL, array('id'=>'toPriceFilter', 'placeholder'=>__($is_job_category ? 'Salary' : 'Price').' '.__('To'), 'autocomplete'=>'off')) ?>
        </div>
        <script type="text/javascript">
            $(function(){
                $('#fromPriceFilter').TipComplete({
                    values : [<?php echo BoardConfig::instance()->priceHints() ?>],
                    prefix: '<?php echo __('From')?>',
                    suffix: '<?php echo BoardConfig::instance()->priceUnitName()?>',
                });
                $('#toPriceFilter').TipComplete({
                    values : [<?php echo BoardConfig::instance()->priceHints() ?>],
                    prefix: '<?php echo __('To')?>',
                    suffix: '<?php echo BoardConfig::instance()->priceUnitName()?>',
                });
            });
        </script>
	<?endif?>
	<?php echo Form::submit(NULL, 'Фильтр', array('id'=>'boardTopSubmit', 'class'=>'boardSubmit'))?>
    <div class="clear"></div>
<?php echo Form::close()?>
</div>