<?php defined('SYSPATH') or die('No direct script access.');?>

<div class="pull-left">
    <h2>Поиск дубликатов</h2>
    <ul class="nav nav-pills">
        <li role="presentation" class="<?php echo $type=='' ? 'active':''?>"><a href="<?php echo URL::base().$route->uri(Arr::merge($route_params, array('action'=>NULL)))?>">Начало</a></li>
        <li role="presentation" class="<?php echo $type=='title' ? 'active':''?>"><a href="<?php echo URL::base().$route->uri(Arr::merge($route_params, array('action'=>'title')))?>">По заголовку</a></li>
        <li role="presentation" class="<?php echo $type=='email' ? 'active':''?>"><a href="<?php echo URL::base().$route->uri(Arr::merge($route_params, array('action'=>'email')))?>">По email</a></li>
        <li role="presentation" class="<?php echo $type=='text' ? 'active':''?>"><a href="<?php echo URL::base().$route->uri(Arr::merge($route_params, array('action'=>'text')))?>">По тексту</a></li>
    </ul>
</div>
<div class="clearfix"></div>
<br>
<div class="well">
    <div>Быстрый переход на авто-удаление:</div>
    <a data-bb="confirm" class="btn btn-warning" href="<?php echo URL::base().$route->uri(Arr::merge($route_params, array('action'=>'auto')))?>?type=text">Удалить по тексту</a>
    <a data-bb="confirm" class="btn btn-warning" href="<?php echo URL::base().$route->uri(Arr::merge($route_params, array('action'=>'auto')))?>?type=title">Удалить по заголовку</a>
    <a data-bb="confirm" class="btn btn-warning" href="<?php echo URL::base().$route->uri(Arr::merge($route_params, array('action'=>'auto')))?>?type=email">Удалить по пользователю</a>
</div>