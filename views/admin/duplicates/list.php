<?php defined('SYSPATH') or die('No direct script access.');?>

<div class="pull-left">
    <h2>Поиск дубликатов</h2>
    <ul class="nav nav-pills">
        <li role="presentation"><a href="<?php echo URL::base().$route->uri(Arr::merge($route_params, array('action'=>NULL)))?>">Начало</a></li>
        <li role="presentation" class="<?php echo $type=='title' ? 'active':''?>"><a href="<?php echo URL::base().$route->uri(Arr::merge($route_params, array('action'=>'title')))?>">По заголовку</a></li>
        <li role="presentation" class="<?php echo $type=='email' ? 'active':''?>"><a href="<?php echo URL::base().$route->uri(Arr::merge($route_params, array('action'=>'email')))?>">По email</a></li>
        <li role="presentation" class="<?php echo $type=='text' ? 'active':''?>"><a href="<?php echo URL::base().$route->uri(Arr::merge($route_params, array('action'=>'text')))?>">По тексту</a></li>
    </ul>
</div>
<div class="pull-right text-right">
    <?php echo $pagination->render()?>
    <div>
        <a data-bb="confirm" href="<?=URL::site( $route->uri(Arr::merge($route_params, array('action'=>'auto'))).URL::query(array('type'=>$type)))?>" class='btn btn-warning' title=''>Очистить автоматически</a>
    </div>
</div>
<div class="clearfix"></div>
<Hr>
<div class="well">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Количество</th>
            <th>Дубликат</th>
            <th><?php echo __('Operations')?></th>
<!--            <th><input type="checkbox" value="1" id="toggle_checkbox"></th>-->
        </tr>
        </thead>
        <?if(!count($items)):?>
            <tr><td colspan="4"><?php echo __('Nothing found')?></td></tr>
        <?endif;?>
        <? foreach($items as $item): ?>
            <tr>
                <td><?php echo $item['cnt']?></td>
                <td><?php echo $item['subtext']?></td>
                <td style="width: 150px;">
                    <div class="btn-group">
                        <?if($type == 'email'):?>
                            <a target="_blank" href="<?=URL::site('/admin/board/?user_id='.$item['user_id'])?>" class='btn btn-inverse' title='Показать ТОП 100'><i class="glyphicon glyphicon-stats"></i></a>
                        <?else:?>
                            <a target="_blank" href="<?=URL::site( $route->uri(Arr::merge($route_params, array('action'=>'top100'))).URL::query(array('type'=>$type, 'value'=>$item['subtext'])))?>" class='btn btn-inverse' title='Показать ТОП 100'><i class="glyphicon glyphicon-stats"></i></a>
                        <?endif?>
                    </div>
                </td>
<!--                <td><input type="checkbox" name="operate[]" value="--><?php //echo $item['subtext']?><!--"></td>-->
            </tr>
        <? endforeach; ?>
    </table>
</div>
<?php echo Form::close()?>
<div class="clearfix"></div>