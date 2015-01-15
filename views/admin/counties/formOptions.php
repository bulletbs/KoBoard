<?php defined('SYSPATH') or die('No direct script access.');?>
<div id="filterOptions">
    <div id="optionsList">
    <?foreach($model->cities->find_all() as $option):?>
    <div>
        <?=Form::input('options['.$option->id.']', $option->name, array('class'=>'span4'))?>&nbsp;<?= Form::input('del','X',array('class'=>'del', 'type'=>'button'))?><br>
    </div>
    <?endforeach;?>
    </div>
    <br>
    <?= Form::input('add', 'Add option', array('id'=>'addButton', 'type'=>'button'))?>
</div>
<div id="deletedOptions"></div>

<script type="text/javascript">
optionHtml = '<div><?= Form::input('newOptions[]', '', array('class'=>'span4'))?>&nbsp;<?= Form::input('del','X',array('class'=>'del', 'type'=>'button'))?><br></div>';
deletedOptionHtml = '<?= Form::hidden('deleted[]', 'optionKey') ?>';
</script>