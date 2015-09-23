<?php defined('SYSPATH') or die('No direct script access.');?>
<div id="filterOptions">
    <div id="optionsList">
    <?foreach($model->children()->as_array('id') as $option):?>
    <div>
        <?=Form::input('options['.$option->id.']', $option->name, array('class'=>'span4'))?>&nbsp;<?= Form::input('del','X',array('class'=>'del btn', 'type'=>'button'))?><br>
    </div>
    <?endforeach;?>
    </div>
    <br>
    <?= Form::input('add', __('Add category'), array('type'=>'button', 'class'=>'btn addButton'))?>
</div>
<div id="deletedOptions"></div>

<script type="text/javascript">
optionHtml = '<div><?= Form::input('newOptions[]', '', array('class'=>'span4'))?>&nbsp;<?= Form::input('del','X',array('class'=>'del btn', 'type'=>'button'))?><br></div>';
deletedOptionHtml = '<?= Form::hidden('deleted[]', 'optionKey') ?>';
</script>