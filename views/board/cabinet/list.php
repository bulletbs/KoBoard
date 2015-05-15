<?php defined('SYSPATH') or die('No direct script access.');?>
<h1><?php echo __('My ads') ?></h1>

<?= Flash::render('global/flash') ?>

<a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'edit'))?>" class="pure-button pure-button-primary right"><?php echo __('Add ad') ?></a>
<br class="clear">
<br class="clear">
<table class="gray content_full">
<thead>
    <tr>
        <th width="3%">№</th>
        <th>Заголовок</th>
        <th width="20%">Действия</th>
    </tr>
</thead>
<tbody>
<?if(count($ads)):?>
<?foreach($ads as $ad):?>
    <tr>
        <td><?php echo $ad->id?></td>
        <td><?php echo HTML::anchor($ad->getUri(), $ad->title, array('target'=>'_blank'))?></td>
        <td>
            <a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'edit', 'id'=>$ad->id))?>" class='pure-button pure-button' title="<?php echo __('Edit')?>"><i class="fa fa-edit"></i></a>
            <a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'enable', 'id'=>$ad->id))?>" class='pure-button pure-button' title="<?php echo __('Status')?>"><i class="fa fa-eye<?php echo !$ad->publish ? '-slash' : ''?>"></i></a>
            <a href="<?php echo URL::site().Route::get('board_myads')->uri(array('action'=>'remove', 'id'=>$ad->id))?>" class='pure-button pure-button-error' title="<?php echo __('Delete')?>"><i class="fa fa-trash-o"></i></a>
        </td>
    </tr>
<?endforeach?>
<?else:?>
<tr>
    <td colspan="3" class="message"><?php echo __('No ads') ?></td>
</tr>
<?endif?>
</tbody>
</table>
<script type="text/javascript">
$('.pure-button-error').on('click', function(e){
    if(!confirm('<?php echo __('Are you sure?')?>'))
        e.preventDefault();
});
</script>