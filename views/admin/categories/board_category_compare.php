<?php defined('SYSPATH') or die('No direct script access.');?>

<h2><?php echo __('Move ads')?></h2>

<?php echo Form::open('')?>
<div class="pull-right">
    <?php echo Form::button('move', __('Move ads'), array('class'=>'btn btn-danger', 'data-bb'=>'confirm'))?>
    <?php echo Form::button('save', __('Save'), array('class'=>'btn btn-success'))?>
</div>
<div class="clearfix"></div>
<div class="row">&nbsp;</div>

<div class="well">
<table class="table table-striped table-condensed">
    <thead>
        <tr>
            <th>Старая категроия</th>
            <th>Перенести в</th>
        </tr>
    </thead>
    <tbody>
    <?foreach($old_categories as $_category):?>
    <tr>
        <td><?php echo $_category->getLeveledName()?></td>
        <td><?php echo $_category->parent_id>0 ? Form::select('move_to['.$_category->id.']', $categories_options, $_category->new_id) : ''?></td>
    </tr>
    <?endforeach?>
    </tbody>
</table>
<?php echo Form::close() ?>
</div>