<?php defined('SYSPATH') or die('No direct script access.');?>
<h2><?php echo $moderate_name?></h2>

<?= $pagination->render()?>

<?php echo Form::open(URL::site( $moderate_uri.'/multi'))?>
<?if(count($items)):?>
    <div class="pull-right">
        <?php echo HTML::anchor($moderate_uri.'/checkall', __('Check all'), array('class'=>'btn btn-primary'))?>
        <?if(count($items)):?><?php echo Form::button('delete_all', __('Delete selected'), array('class'=>'btn btn-danger', 'data-bb'=>'confirm'))?><?endif?>
        <?if(count($items)):?><?php echo Form::button('check_all', __('Check selected'), array('class'=>'btn btn-success'))?><?endif?>
    </div>
    <div class="clearfix"></div>
    <div class="row">&nbsp;</div>
<?endif;?>

<div class="well">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <? foreach($list_fields as $field): ?>
                <th><?php echo $labels[$field]?></th>
            <?endforeach?>
            <th><?php echo __('Operations')?></th>
            <th><input type="checkbox" value="1" id="toggle_checkbox"></th>
        </tr>
        </thead>
        <?if(!count($items)):?>
            <tr><td colspan="4"><?php echo __('Nothing found')?></td></tr>
        <?endif;?>
        <? foreach($items as $item): ?>
            <tr>
                <td><?=$item->id?></td>
                <? foreach($list_fields as $field): ?>
                    <td><?php echo $item->$field ?></td><?endforeach?>
                <td style="width: 150px;">
                    <div class="btn-group">
                        <a href="<?=URL::site( $crud_uri.'/edit/'.$item->id . URL::query())?>" class='btn btn-inverse' title='<?=__('Edit')?>'><i class="glyphicon glyphicon-edit"></i></a>
                        <a data-bb="confirm" href="<?=URL::site( $moderate_uri.'/delete/'.$item->id . URL::query())?>" class='btn btn-inverse' title='<?=__('Delete')?>'><i class="glyphicon glyphicon-trash"></i></a>
                        <?if(!$item->$moderate_field):?><a href="<?=URL::site( $moderate_uri.'/check/'.$item->id . URL::query())?>" class='btn btn-inverse' title='<?=__('Moderate')?>'><i class="glyphicon glyphicon-check"></i></a><?endif?>
                    </div>
                </td>
                <td><input type="checkbox" name="operate[]" value="<?php echo $item->id?>"></td>
            </tr>
        <? endforeach; ?>
    </table>
    <?php echo Form::close()?>
</div>