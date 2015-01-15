
<div id="optionsList">
    <div id="deletedOptions"></div>
    <?= $model->name?>
    <?foreach($model->options->find_all() as $option):?>
        <div>
            <?=Form::input('options['.$option->id.']', $option->value, array('class'=>'span4'))?>&nbsp;<?= Form::input('del','X',array('class'=>'btn del', 'type'=>'button'))?><br>
        </div>
    <?endforeach;?>
</div>
<br>
<?= Form::input('add', __('Add option'), array('id'=>'addButton', 'type'=>'button', 'class'=>'btn'))?>

<script type="text/javascript">
    optionHtml = '<div><?= Form::input('newOptions[]', '', array('class'=>'span4'))?>&nbsp;<?= Form::input('del','X',array('class'=>'btn del', 'type'=>'button'))?><br></div>';
    deletedOptionHtml = '<?= Form::hidden('deleted[]', 'optionKey') ?>';
</script>