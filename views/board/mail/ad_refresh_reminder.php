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
Объявления, которые не обновлялись более <?php echo $days?> дней:<br />
<?foreach($ads as $_ad):?>
- <?php echo HTML::anchor($_ad->getUrl(), $_ad->title)?><br />
<?endforeach?>
<?endforeach?><br />
Если Вы желаете обновить объявление, зайдите в свой <a href="<?php echo $server_name ?>my-ads">Личный кабинет</a>.<br />
Продлить объявление можно с помощью кнопки "Обновить".<br />
<br />
---------------------<br />
Перейдите по следующей ссылке, управления объявлениями:<br />
<a href="<?php echo $server_name ?>my-ads"><?php echo $server_name?>my-ads</a><br />
---------------------<br />
<br />
<br />
С уважением,<br />
Администрация сайта <a href="<?php echo $server_name ?>"><?php echo $site_name ?></a><br/>
---<br/>
Если Вы не желаете больше получать наши письма - перейдите по ссылке ниже.<br>
<a href="<?php echo $unsubscribe_link?>">Отписаться от рассылки</a><br />
Внимание! Указаная ссылка действительна только в течении 14 дней с момента получения
</body>
</html>
