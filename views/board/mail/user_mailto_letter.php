<?php defined('SYSPATH') or die('No direct script access.');?>
<html>
<head>
    <title></title>
</head>
<body>
<b>Здравствуйте, <?php echo $name?>!</b><br />
<br />
Вам отправили соообщение со страницы вашего объявления.<br />
==========================================<br />
<b>Контактный e-mail: </b><a href="mailto:<?php echo $email?>"><?php echo $email?></a><br />
<b>Текст сообщения: </b><br /><?php echo nl2br($text)?></a><br />
<br />
==========================================<br />
С уважением,<br />
Администрация сайта <?php echo $site_name ?><br/>
<br/>
<small>Если Вы не желаете больше получать наши письма - перейдите по ссылке ниже.<br>
<a href="http://<?php echo $server_name ?>/<?php echo $unsubscribe_link?>">Отписаться от рассылки</a><br />
Внимание! Указаная ссылка действительна только в течении 14 дней с момента получения<small>
</body>
</html>