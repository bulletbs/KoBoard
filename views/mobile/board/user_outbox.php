<?php defined('SYSPATH') or die('No direct script access.');?>

<?=Form::open('/board/send_message/'. $ad_id, array('class' => 'uk-form  uk-form-stacked uk-margin-top', 'enctype' => 'multipart/form-data','id'=>'mailtoForm'))?>
    <legend><?php echo __('Send message to user')?></legend>
    <?if(isset($errors)) echo View::factory('mobile/error/validation', array('errors'=>$errors))->render()?>

    <?if(!Auth::instance()->logged_in('login')):?>
    <div class="uk-form-row">
        <?php echo  Form::label('email', __('Your e-mail'), array('class'=>'uk-form-label'))?>
        <div class="uk-form-controls">  <?php echo  Form::input('email', Arr::get($_POST, 'email'), array('class'=>isset($errors['title']) ? 'error-input': '', 'id'=>'mailto-email'))?></div>
    </div>
    <?endif?>
    <div class="uk-form-row">
        <?php echo  Form::label('text', __('Message text'), array('class'=>'uk-form-label'))?>
        <div class="uk-form-controls">  <?php echo  Form::textarea('text', Arr::get($_POST, 'text'), array('class'=>isset($errors['title']) ? 'error-input': '', 'id'=>'mailto-text'))?></div>
    </div>
    <?if(!Auth::instance()->logged_in()):?>
        <div class="uk-form-row">
            <?php echo Captcha::instance(); ?>
        </div>
    <?endif?>
    <?=Form::hidden('update', 1);  ?>
    <div class="uk-form-row uk-margin">
        <?=Form::button('update_button', __('Send message'), array('class' => 'uk-button uk-button-primary'));  ?>
        <?=Form::button('cancel', __('Cancel'), array('class' => 'uk-button uk-button-error', 'id'=>'cancel_mailto'));  ?>
    </div>
<?=Form::close()?>