<?php defined('SYSPATH') or die('No direct script access.');?>
<h2><?php echo __('Abuses')?></h2>

<?= $pagination->render()?>

<?php echo Form::open(URL::site( 'admin/boardAbuses/multi'))?>
<?if(count($abuses)):?>
<div class="clearfix"></div>
<div class="row">&nbsp;</div>
<?endif;?>

<?if(count($abuses)):?>
    <div class="pull-right">
        <?php echo HTML::anchor('admin/boardAbuses/delwads', __('Delete all with ads'), array('class'=>'btn btn-danger', 'data-bb'=>'confirm'))?>
        <?php echo HTML::anchor('admin/boardAbuses/delall', __('Delete all'), array('class'=>'btn btn-danger', 'data-bb'=>'confirm'))?>
        <?php echo Form::button('delete_all', __('Delete selected'), array('class'=>'btn btn-warning', 'data-bb'=>'confirm'))?>
    </div>
    <div class="clearfix"></div>
    <div class="row">&nbsp;</div>
<?endif;?>

<div class="well">
<table class="table table-striped">
<thead>
    <tr>
        <th>ID</th>
        <th><?php echo __('Abuse type')?></th>
        <th><?php echo __('Ad title')?></th>
        <th><?php echo __('Operations')?></th>
        <th><input type="checkbox" value="1" id="toggle_checkbox"></th>
    </tr>
</thead>
<? foreach($abuses as $abuse): ?>
    <tr>
        <td><?= $abuse->id ?></td>
        <td><?= Model_BoardAbuse::$types[ $abuse->type ] ?></td>
        <td><?= $abuse->ad->loaded() ? HTML::anchor($abuse->ad->getUri(), $abuse->ad->title, array('target'=>'_blank')) : "Объявление удалено" ?></td>
        <td style="width: 150px;">
            <div class="btn-group">
                <a data-bb="confirm" href="<?=URL::site( 'admin/boardAbuses/remove/'.$abuse->id . URL::query())?>" class='btn btn-inverse' title='<?=__('Delete with ad')?>'><i class="glyphicon glyphicon-remove"></i></a>
                <a data-bb="confirm" href="<?=URL::site( 'admin/boardAbuses/delete/'.$abuse->id . URL::query())?>" class='btn btn-inverse' title='<?=__('Delete')?>'><i class="glyphicon glyphicon-trash"></i></a>
            </div>
        </td>
        <td><input type="checkbox" name="operate[]" value="<?php echo $abuse->id?>"></td>
    </tr>
<? endforeach; ?>
</table>
<?php echo Form::close()?>
</div>