<?php defined('SYSPATH') or die('No direct script access.');?>

<div class="pull-left">
    <h2>ТОП 100 совпадений по значению</h2>
    <blockquote><?php echo $value?></blockquote>
</div>
<div class="pull-right text-right">
    <?php echo $pagination->render()?>
    <div>
        <a data-bb="confirm" href="<?=URL::site( $route->uri(Arr::merge($route_params, array('action'=>'clearall'))).URL::query(array('all'=>1)))?>" class='btn btn-danger' title=''>Удалить все</a>
        <a data-bb="confirm" href="<?=URL::site( $route->uri(Arr::merge($route_params, array('action'=>'clearall'))).URL::query())?>" class='btn btn-warning' title=''>Очистить все</a>
    </div>
</div>
<div class="clearfix"></div>
<Hr>

<div class="well">
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Количество</th>
            <th>Автор</th>
            <th><?php echo __('Operations')?></th>
        </tr>
        </thead>
        <?if(!count($items)):?>
            <tr><td colspan="4"><?php echo __('Nothing found')?></td></tr>
        <?endif;?>
        <? foreach($items as $item): ?>
            <tr>
                <td><?php echo $item['cnt']?></td>
                <td><?php echo isset($users[$item['val']]) ? HTML::anchor('/admin/board/?user_id='.$item['val'], $users[$item['val']], array('target'=>'_blank')) : $item['val']?></td>
                <td style="width: 150px;">
                    <div class="btn-group">
                        <a target="_blank" data-bb="confirm" href="<?=URL::site( $route->uri(Arr::merge($route_params, array('action'=>'top100'))).URL::query(array('delete'=>1,'value'=>$value, 'subvalue'=>$item['val'] )))?>" class='btn btn-inverse' title='Удалить дубликаты'><i class="glyphicon glyphicon-fire"></i></a>
                        <a target="_blank" data-bb="confirm" href="<?=URL::site( $route->uri(Arr::merge($route_params, array('action'=>'top100'))).URL::query(array('delete'=>1,'value'=>$value, 'subvalue'=>$item['val'], 'all'=>1 )))?>" class='btn btn-inverse' title='Удалить все'><i class="glyphicon glyphicon-remove-circle"></i></a>
                    </div>
                </td>
            </tr>
        <? endforeach; ?>
    </table>
</div>
<?php echo Form::close()?>
<div class="clearfix"></div>