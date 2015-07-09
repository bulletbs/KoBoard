
Родительский фильтр
<?php echo Form::select('parent_id', $parent_filters, $model->parent_id, array('id'=>"parentFilter"))?>
<div id="optionsList">
    <div id="deletedOptions"></div>
    <?foreach($parent_options as $parent_option_id=>$parent_option):?>
    <h3><?php echo $parent_option ?></h3>
        <?if(isset($options[$parent_option_id])):?>
            <?foreach($options[$parent_option_id] as $option):?>
            <div><?=Form::input('options['.$option->id.']', $option->value, array('class'=>'span4'))?>&nbsp;<?= Form::input('del','X',array('class'=>'btn del', 'type'=>'button', 'data-id'=>$option->id, 'data-parent-id'=>$parent_option_id))?></div>
            <? endforeach ?>
        <?endif?>
        <?= Form::input('add', __('Add option'), array('type'=>'button', 'class'=>'btn addButton', 'data-id'=>$parent_option_id))?>
        <hr>
    <? endforeach ?>
</div>
<br>
<script type="text/javascript">
    optionHtml = '<div><?= Form::input('newOptions[PARENT_ID][]', '', array('class'=>'span4'))?>&nbsp;<?= Form::input('del','X',array('class'=>'btn del', 'type'=>'button'))?><br></div>';
    deletedOptionHtml = '<?= Form::hidden('deleted[]', 'optionKey') ?>';
</script>