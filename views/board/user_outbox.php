<?php defined('SYSPATH') or die('No direct script access.');?>

<?=Form::open('/board/send_message/'. $ad_id, array('class' => 'pure-form  pure-form-stacked', 'enctype' => 'multipart/form-data','id'=>'mailtoForm'))?>
    <fieldset>
        <legend><?php echo __('Send message to user')?></legend>
        <?if(isset($errors)) echo View::factory('error/validation', array('errors'=>$errors))->render()?>

        <?if(!Auth::instance()->logged_in('login')):?>
            <?php echo  Form::label('email', __('Your e-mail'))?>
            <?php echo  Form::input('email', Arr::get($_POST, 'email'), array('class'=>isset($errors['title']) ? 'error-input': '', 'id'=>'mailto-email'))?>
        <?endif?>

        <?php echo  Form::label('text', __('Message text'))?>
        <?php echo  Form::textarea('text', Arr::get($_POST, 'text'), array('class'=>isset($errors['title']) ? 'error-input': '', 'id'=>'mailto-text'))?>

        <?if(!Auth::instance()->logged_in()):?>
            <Br><?php echo Captcha::instance() ?>
            <?= Form::label('captcha', __('Enter captcha code')) ?>
            <?php echo Form::input('captcha', NULL, array('id' => 'captcha-key'))?>
        <?endif?>
    <?=Form::submit('update', __('Send message'), array('class' => 'pure-button pure-button-primary'));  ?>
    <?=Form::submit('cancel', __('Cancel'), array('class' => 'pure-button pure-button-error', 'id'=>'cancel_mailto'));  ?>
</fieldset>
<?=Form::close()?>