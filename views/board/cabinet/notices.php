<?php defined('SYSPATH') or die('No direct script access.');?>
<h1><?php echo __('My notices') ?></h1>

<?= Flash::render('global/flash') ?>

<?if(count($notices)):?>
<a href="<?php echo URL::site().Route::get('board_notices')->uri(array('action'=>'notice_clean'))?>" class="pure-button pure-button-primary right"><?php echo __('Remove all') ?></a>
<table class="tableccat tableccat_full">
<?foreach($notices as $notice):?>
    <tr><td class="dashed">
        <table>
            <tr>
                <td class="list_date"><?= Date::smart_date($notice->sendtime)?></td>
                <td class="list_title">
                    <h3><i class="fa fa-exclamation-circle"></i> <?php echo ucfirst(Model_BoardNotice::$types[$notice->type])?></h3>
                    <?php echo $notice->text?>
                </td>
                <td class="list_button">
                    <a href="<?php echo URL::site().Route::get('board_notices')->uri(array('action'=>'notice_remove', 'id'=>$notice->id))?>" class='pure-button pure-button-error' title="<?php echo __('Delete')?>"><i class="fa fa-trash-o"></i></a>
                </td>
            </tr>
        </table>
    </td></tr>
<?endforeach;?>
</table>
<div class="clear"></div>
<?else:?>
<b>У Вас нет оповещений</b>
<?endif?>

<script type="text/javascript">
$('.pure-button-error').on('click', function(e){
    if(!confirm('<?php echo __('Are you sure?')?>'))
        e.preventDefault();
});
</script>