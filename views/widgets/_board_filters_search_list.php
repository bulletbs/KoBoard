<?php defined('SYSPATH') OR die('No direct script access.');?>
<?if(count($filters)):?>
    <?echo Debug::vars($filters)?>
    <br>
    <?foreach($filters as $filter_id=>$data):?>
    <div class="filter">
        <label><?= $data['name'] ?></label>
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
                <?= Form::checkbox('filters['.$filter_id.']['.$i.']', 1, isset($data['value'][$i]) && $data['value'][$i] ? TRUE : FALSE) ?><?php echo $_option?>
                <?$i++?>
            <?endforeach?>
        <?endif;?>
    </div>
    <?endforeach;?>
    <div class="clear"></div>
<?endif;?>
