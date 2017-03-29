<?php defined('SYSPATH') or die('No direct script access.');?>

<h2>Автоматическое удаление дубликатов (по <?php echo $type=='title'?'заголовку':'тексту'?>)</h2>
<div>
    <a href="<?php echo URL::base().$route->uri(Arr::merge($route_params, array('action'=>$type!='title'?$type:NULL)))?>" class='btn btn-warning'>Остановить</a>
</div>
<meta http-equiv="refresh" content="5">
