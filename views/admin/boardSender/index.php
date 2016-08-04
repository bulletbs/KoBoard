<?php defined('SYSPATH') or die('No direct script access.');?>

<?php echo Widget::load('adminBoardMenu')?>

<div class="pull-right">
    <a href="/admin/boardSender/sendtest" data-bb="confirm"  class="btn btn-success"><?php echo __('Test (to :email)', array(':email'=>$admin_email))?></a>
    <?if($last > 0):?><a href="/admin/boardSender/send" data-bb="confirm"  class="btn btn-success"><?php echo __('Send next mail pack (:count)', array(':count'=>$stepcount))?></a><?endif?>
    <a href="/admin/boardSender/create" data-bb="confirm"  class="btn btn-primary"><?php echo __('Create mailer queue')?></a>
</div>
<h3><?php echo __('Mailer')?></h3>
<div class="clearfix"></div>
<div class="well">
 <b><?php echo __('Emails in mailer base')?></b> <?= $total ?><br />
 <b><?php echo __('Last to send') ?></b> <?= $last ?><br />
</div>

<h3>Пример письма</h3>
<div class="well">
    <?php echo $letter ?>
</div>