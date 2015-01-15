<?php defined('SYSPATH') or die('No direct script access.');?>
<div id="filterOptions">
    <div id="optionsList">
    <?foreach($model->children()->as_array('id') as $option):?>
    <div>
        <?=Form::input('options['.$option->id.']', $option->name, array('class'=>'span4'))?>&nbsp;<?= Form::input('del','X',array('class'=>'btn', 'type'=>'button'))?><br>
    </div>
    <?endforeach;?>
    </div>
    <br>
    <?= Form::input('add', __('Add').' '.__('city'), array('id'=>'addButton', 'type'=>'button'))?>
</div>
<div id="deletedOptions"></div>

<script type="text/javascript">
optionHtml = '<div><?= Form::input('newOptions[]', '', array('class'=>'span4'))?>&nbsp;<?= Form::input('del','X',array('class'=>'btn', 'type'=>'button'))?><br></div>';
deletedOptionHtml = '<?= Form::hidden('deleted[]', 'optionKey') ?>';
</script>