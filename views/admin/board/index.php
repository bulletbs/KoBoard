<?php defined('SYSPATH') or die('No direct script access.');?>
<h2><?php echo $crud_name?></h2>

<?= $pagination->render()?>
<?if(count($filter_fields)) echo View::factory('admin/crud/filters', array('filter_fields'=>$filter_fields))->render();?>
<?php echo Form::open(URL::site( $crud_uri.'/multi'))?>
<?if(count($items)):?>
    <div class="pull-right">
        <?if(count($items)):?><?php echo HTML::anchor($crud_uri.'/delall/'.URL::query(), __('Delete all'), array('class'=>'btn btn-danger', 'data-bb'=>'confirm'))?><?endif?>
        <?if(count($items)):?><?php echo Form::button('del_selected', __('Delete selected'), array('class'=>'btn btn-warning', 'data-bb'=>'confirm'))?><?endif?>
    </div>
    <div class="clearfix"></div>
    <div class="row">&nbsp;</div>
<?endif;?>

<div class="well">
    <table class="table table-striped">
        <thead>
        <tr>
            <th><?php echo $labels['title']?></th>
            <th><?php echo $labels['category_id']?></th>
            <th><?php echo $labels['addTime']?></th>
            <th><?php echo $labels['name']?></th>
            <th><?php echo __('Operations')?></th>
            <th><input type="checkbox" value="1" id="toggle_checkbox"></th>
        </tr>
        </thead>
        <?if(!count($items)):?>
            <tr><td colspan="4"><?php echo __('Nothing found')?></td></tr>
        <?endif;?>
        <? foreach($items as $item): ?>
            <tr>
<!--                <td>--><?php //echo $item->id ?><!--</td>-->
                <td>
                <?php echo HTML::anchor($item->getUri(), $item->title, array('target'=>'_blank')) ?>
                <?if(isset($photos[$item->id])):?>
                    <br>
                    <?foreach($photos[$item->id] as $_photo):?>
                        <a target="_blank" href="<?php echo $_photo->getPhotoUri() ?>"><?php echo $_photo->getThumbTag($item->title, array('class'=>'microthumb left')) ?></a>
                    <?endforeach?>
                <?endif?>
                </td>
                <td class="small"><?php echo Model_BoardCategory::getField('name', $item->pcategory_id) ?> &raquo; <?php echo Model_BoardCategory::getField('name', $item->category_id) ?></td>
                <td><?php echo $item->addTime ?></td>
                <td>
                    <?php echo $item->name?>
                    <?if($item->user_id > 0):?>&nbsp;<?php echo HTML::anchor($user_uri.'/edit/'.$item->user_id, '<i class="glyphicon glyphicon-user"></i>', array('target'=>'_blank', 'title'=>__('Edit user')))?><?endif?>&nbsp;
                    <?if($item->user_id > 0):?><?php echo HTML::anchor($crud_uri.'?user_id='.$item->user_id, '<i class="glyphicon glyphicon-list"></i>', array('target'=>'_blank', 'class'=>'', 'title'=>__('All user ads')))?><?endif?>&nbsp;
                    <?if($item->user_id > 0):?><?php echo HTML::anchor($user_uri.'/userin/'.$item->user_id, '<i class="glyphicon glyphicon-log-in"></i>', array('class'=>'', 'title'=>__('Login as user')))?><?endif?>
                </td>
                <td style="width: 150px;">
                    <div class="btn-group">
                        <a href="<?=URL::site( $crud_uri.'/edit/'.$item->id . URL::query())?>" class='btn btn-inverse' title='<?=__('Edit')?>'><i class="glyphicon glyphicon-edit"></i></a>
                        <a data-bb="confirm" href="<?=URL::site( $crud_uri.'/delete/'.$item->id . URL::query())?>" class='btn btn-inverse' title='<?=__('Delete')?>'><i class="glyphicon glyphicon-trash"></i></a>
                    </div>
                </td>
                <td><input type="checkbox" name="operate[]" value="<?php echo $item->id?>"></td>
            </tr>
        <? endforeach; ?>
    </table>
</div>
<?= $pagination->render()?>
<?if(count($items)):?>
    <div class="pull-right">
        <?if(count($items)):?><?php echo Form::button('del_selected', __('Delete selected'), array('class'=>'btn btn-danger', 'data-bb'=>'confirm'))?><?endif?>
    </div>
<?endif;?>
<?php echo Form::close()?>
<div class="clearfix"></div>