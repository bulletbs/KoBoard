<?php defined('SYSPATH') or die('No direct script access.');?>

<?if(count($filters)):?>
<?foreach($filters as $filter_id=>$data):?>
    <div class="control-group">
        <div class="control-label"><?= $data['name'] ?></div>
        <div class="controls">
            <? if($data['type'] == 'select'): ?>
                <?= Form::select('filters['.$filter_id.']', isset($data['options']) ? $data['options'] : array(), isset($data['value']) ? $data['value'] : NULL, array('data-id'=>$filter_id)) ?>
            <? elseif($data['type'] == 'childlist'): ?>
                <?= Form::select('filters['.$filter_id.']', isset($data['options']) ? $data['options'] : array(), isset($data['value']) ? $data['value'] : NULL, array('data-id'=>$filter_id, 'data-parent'=>$data['parent'])) ?>
            <? elseif($data['type'] == 'text'): ?>
                <?= Form::input('filters['.$filter_id.']', isset($data['value']) ? $data['value'] : NULL) ?>
            <? elseif($data['type'] == 'digit'): ?>
                <?= Form::input('filters['.$filter_id.']', isset($data['value']) ? $data['value'] : NULL) ?>
            <? elseif($data['type'] == 'checkbox'): ?>
                <?= Form::checkbox('filters['.$filter_id.']', 1, isset($data['value']) && $data['value'] ? TRUE : FALSE) ?>
            <? elseif($data['type'] == 'optlist'): ?>
                <?$i = 0;?>
                <?foreach($data['options'] as $_option_id=>$_option):?>
                    <?= Form::checkbox('filters['.$filter_id.']['.$i.']', 1, isset($data['value'][$i]) && $data['value'][$i] ? TRUE : FALSE) ?> <?php echo $_option?><br>
                    <?$i++?>
                <?endforeach?>
            <?endif;?>
        </div>
    </div>
<?endforeach;?>
<?else:?>
    <span class="info"><?php echo _('No filters found in selected category')?></span>
<?endif;?>