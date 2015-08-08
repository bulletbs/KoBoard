<?php defined('SYSPATH') or die('No direct script access.');?>

<html>
<head>
    <title></title>
</head>
<body>
<b>Здравствуйте!</b><br />
<br />
Вы или кто-то от Вашего имени разместил объявление на сайте «<?php echo $site_name ?>».<br />
Если Вы не размещали, просто проигнорируйте это сообщение.<br />
<br />
---------------------<br />
Перейдите по следующей ссылке, для подтверждения публикации:<br />
<a href="http://<?php echo $server_name ?>/<?php echo $activation_link ?>">http://<?php echo $server_name ?>/<?php echo $activation_link ?></a><br />
---------------------<br />
<?if(!is_null($password)):?>
Для управления своими объявлениями используйте следующие данные:<br />
Логин: <?php echo $user->email?><br />
Пароль: <?php echo $password?><br />
---------------------<br />
<?endif?>
<br />
<br />
С уважением,<br />
Администрация сайта <?php echo $site_name ?>
</body>
</html>