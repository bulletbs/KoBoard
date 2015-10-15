<?php defined('SYSPATH') or die('No direct script access.');?>

<html>
<head>
    <title></title>
</head>
<body>
<b>Здравствуйте!</b><br />
<br />
Некоторые из Ваших объявлений на сайте «<?php echo $site_name ?>» возможно устарели.<br>
<?foreach($user_ads as $days=>$ads):?>
<br>
Объявления, которые не обновлялись <?php echo $days?> дней:<br />
<?foreach($ads as $_ad):?>
- <?php echo HTML::anchor('http://'. $server_name .$_ad->getUri(), $_ad->title)?><br />
<?endforeach?>
<?endforeach?><br />
Если Вы желаете обновить объявление, зайдите в свой <a href="http://<?php echo $server_name ?>/my-ads">Личный кабинет</a>.<br />
Продлить объявление можно с помощью кнопки "Обновить".<br />
<br />
---------------------<br />
Перейдите по следующей ссылке, управления объявлениями:<br />
<a href="http://<?php echo $server_name ?>/my-ads">http://<?php echo $server_name ?>/my-ads</a><br />
---------------------<br />
<br />
<br />
С уважением,<br />
Администрация сайта <a href="http://<?php echo $server_name ?>"><?php echo $site_name ?></a>
</body>
</html>
