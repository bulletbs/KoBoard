<?php defined('SYSPATH') or die('No direct script access.');?>
<html>
<head>
    <title></title>
</head>
<body>
<b>Здравствуйте, <?php echo $name?>!</b><br />
<br />
Вам отправили соообщение об &laquo;<?php echo $title?>&raquo;.<br />

==========================================<br />
Прочитать сообщение возможно по ссылке:
<?php echo HTML::anchor($dialog_link, $title) ?><br />
Внимание! Указаная ссылка действительна только в течении трех дней с момента получения<br />
==========================================<br />
С уважением,<br />
Администрация сайта <?php echo $site_name ?><br/>
<br/>
<small>Если Вы не желаете больше получать наши письма - перейдите по ссылке ниже.<br>
<a href="<?php echo $server_name ?>/<?php echo $unsubscribe_link?>">Отписаться от рассылки</a><br />
Внимание! Указаная ссылка действительна только в течении трех дней с момента получения<small>
</body>
</html>