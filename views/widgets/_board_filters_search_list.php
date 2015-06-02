<?php defined('SYSPATH') OR die('No direct script access.');?>

<?if(count($filters)):?>
    <?foreach($filters as $filter_id=>$data):?>
    <div class="filter">
<!--        <label>--><?//= $data['name'] ?><!--</label>-->
        <? if($data['type'] == 'select' && isset($data['is_parent'])): ?>
            <?
            $params = array('data-id'=>$filter_id);
            if(isset($data['main']) && $data['main'] > 0) $params['data-main'] = 1;
            ?>
            <?= Form::select('filters['.$filter_id.']', isset($data['options']) ? $data['options'] : array(), isset($data['value']) ? $data['value'] : NULL, $params) ?>
        <? elseif($data['type'] == 'childlist'): ?>
            <?= Form::select('filters['.$filter_id.']', isset($data['options']) ? $data['options'] : array(), isset($data['value']) ? $data['value'] : NULL, Arr::merge(array('data-id'=>$filter_id, 'data-parent'=>$data['parent']), !isset($data['options']) || !count($data['options']) ? array('disabled'=>'disabled') : array()) ) ?>
        <? elseif($data['type'] == 'select'): ?>
            <?= Form::select('filters['.$filter_id.'][]', isset($data['options']) ? $data['options'] : array(), isset($data['value']) ? $data['value'] : NULL, array('id'=>'searchFilter'.$filter_id, 'data-id'=>$filter_id, 'multiple'=>'multiple', 'placeholder'=>$data['name'])) ?>
            <script type="text/javascript">
            $(function() { $('#<?php echo 'searchFilter'.$filter_id?>').multipleSelect({
                selectAllText: '<?php echo __('Any')?>',
                allSelected: '<?php echo __('Any')?>',
                countSelected: '# из %',
                selectAll: false,
                placeholder: '<?php echo $data['name']?>'
            }); });
            </script>
        <? elseif($data['type'] == 'text'): ?>
            <?= Form::input('filters['.$filter_id.']', isset($data['value']) ? $data['value'] : NULL) ?>
        <? elseif($data['type'] == 'digit'): ?>
            <?= Form::input('filters['.$filter_id.'][from]', isset($data['value']['from']) ? $data['value']['from'] : NULL, array('id'=>'fromFilter'.$filter_id, 'placeholder'=>$data['name'] .' '. __('From'), 'autocomplete'=>'off')) ?>
            <?= Form::input('filters['.$filter_id.'][to]', isset($data['value']['to']) ? $data['value']['to'] : NULL, array('id'=>'toFilter'.$filter_id, 'placeholder'=>$data['name'] .' '.__('To'), 'autocomplete'=>'off')) ?>
            <script type="text/javascript">
                $('#fromFilter<?php echo $filter_id?>').TipComplete({
                    values : [<?php echo $data['hints'] ?>],
                    prefix: '<?php echo __('From')?>',
                    suffix: '<?php echo $data['units']?>',
                    no_digits: '<?php echo $data['no_digits']?>'
                });
                $('#toFilter<?php echo $filter_id?>').TipComplete({
                    values : [<?php echo $data['hints'] ?>],
                    prefix: '<?php echo __('From')?>',
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
