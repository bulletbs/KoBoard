<?php defined('SYSPATH') OR die('No direct script access.');?>

<?php echo Form::open('')?>
<div class="pull-right">
    <button type="submit" name="submit" class="btn btn-primary">Сохранить</button>    <button name="cancel" class="btn">Отмена</button>
</div>
<h3>Добавить теги</h3>

<fieldset class="well the-fieldset">
    <div class="form-group">
        <label class="control-label">Категория</label>
        <?php echo Form::select('category_id', $category_options, Arr::get($_GET, 'category_id', 0), array('class'=>'form-control input_long'))?>
    </div>
    <div class="form-group">
        <label>Количество</label>
        <?php echo Form::input('cnt', 1000, array('class'=>'form-control'))?>
    </div>
    <div class="form-group">
        <label>Теги (через новую строку)</label>
        <?php echo Form::textarea('tags', NULL, array('class'=>'form-control'))?>
    </div>
</fieldset>
    <div class="pull-right">
        <button type="submit" name="submit" class="btn btn-primary">Сохранить</button>    <button name="cancel" class="btn">Отмена</button>    <br><br>
    </div>
<?php echo Form::close()?>