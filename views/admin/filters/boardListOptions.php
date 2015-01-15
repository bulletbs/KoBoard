
<div id="optionsList">
    <div id="deletedOptions"></div>
    <?foreach($model->options->find_all() as $option):?>
    <div>
        <?=Form::input('options['.$option->id.']', $option->value, array('class'=>'span4'))?>&nbsp;<?= Form::input('del','X',array('class'=>'btn del', 'type'=>'button', 'data-id'=>$option->id))?><br>
    </div>
    <?endforeach;?>
    <?= Form::input('add', __('Add option'), array('type'=>'button', 'class'=>'btn addButton'))?>
</div>
<br>

<script type="text/javascript">
    optionHtml = '<div><?= Form::input('newOptions[]', '', array('class'=>'span4'))?>&nbsp;<?= Form::input('del','X',array('class'=>'btn del', 'type'=>'button'))?><br></div>';
    deletedOptionHtml = '<?= Form::hidden('deleted[]', 'optionKey') ?>';
</script>