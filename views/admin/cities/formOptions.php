<?php defined('SYSPATH') or die('No direct script access.');?>

<div class="clearfix">
    <b class="span2">Название</b>
    <b class="span2">Предложный</b>
    <b class="span2">Родительный</b>
</div>
<div id="filterOptions">
    <div id="optionsList">
    <?foreach($model->children()->as_array('id') as $option):?>
    <div>
        <?=Form::input('options['.$option->id.']', $option->name, array('class'=>'span2'))?>&nbsp;
        <?=Form::input('options_in['.$option->id.']', $option->name_in, array('class'=>'span2'))?>&nbsp;
        <?=Form::input('options_of['.$option->id.']', $option->name_of, array('class'=>'span2'))?>&nbsp;
        <?= Form::button('del','X',array('class'=>'del btn', 'data-id'=>$option->id))?><br>
    </div>
    <?endforeach;?>
    </div>
    <br>
    <?= Form::input('add', __('Add').' '.__('city'), array('class'=>'addButton', 'type'=>'button'))?>
</div>
<div id="deletedOptions"></div>

<script type="text/javascript">
optionHtml = '<div>\
    <?= Form::input('newOptions[]', '', array('class'=>'span2'))?>&nbsp;\
    <?= Form::input('newOptions_in[]', '', array('class'=>'span2'))?>&nbsp;\
    <?= Form::input('newOptions_of[]', '', array('class'=>'span2'))?>&nbsp;\
    <?= Form::button('del','X',array('class'=>'del btn'))?><br>\
</div>';
deletedOptionHtml = '<?= Form::hidden('deleted[]', 'optionKey') ?>';
</script>
<br>
<div class="form-group" id="control_group_alias">
    <?php echo Form::label('multiadd', 'Добавить несколько городов (каждый город с новой строки)', array('class'=>'control-label'))?>
    <?php echo Form::textarea('multiadd', NULL, array('class'=>'form-control'))?>
</div>