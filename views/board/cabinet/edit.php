<?php defined('SYSPATH') or die('No direct script access.');?>
<h1><?php echo __('Ad edit')?></h1>
<?=Form::open('', array('class' => 'pure-form  pure-form-stacked', 'enctype' => 'multipart/form-data','id'=>'addForm'))?>
<?if(isset($errors)) echo View::factory('error/validation', array('errors'=>$errors))->render()?>
    <fieldset>
        <legend>Подать бесплатное объявление</legend>
        <?= Form::label('title', 'Заголовок')?>
        <?= Form::input('title', Arr::get($_POST,'title', $model->title), array('class'=>isset($errors['title']) ? 'error-input': ''))?>

        <?= Form::label('maincategory_id', 'Категория')?>
        <?= Form::select('maincategory_id', $categories, Arr::get($_POST,'maincategory_id', $maincategory_id) , array('class'=>isset($errors['category_id']) ? 'error-input': '', 'id'=>'mainCategory')) ?>
        <div id="filter_holder"><?= $filters ?></div>

        <?= Form::label('description', 'Описание')?>
        <?= Form::textarea('description', Arr::get($_POST,'description', $model->description), array('class'=>isset($errors['description']) ? 'error-input': ''))?>

        <?= Form::label('price', 'Цена ('.$price_value.')')?>
        <?= Form::input('price', Arr::get($_POST,'price', $model->price), array('class'=>'span1 first' . (isset($errors['price']) ? 'error-input': ''))) ?>

        <legend>Фотографии</legend>
        <?if(count($photos)):?>
            <?foreach($photos as $photo):?>
                <div class="pure-u-4-24">
                    <?= HTML::anchor($photo->getPhotoUri(), $photo->getThumbTag('',array('class'=>'thumbnail')), array('target'=>'_blank')) ?>
                    <?= FORM::checkbox('delphotos[]', $photo->id, FALSE)?> удалить<br>
                    <?= FORM::radio('setmain', $photo->id, $photo->main == 1)?> основная
                </div>
            <?endforeach;?>
            <legend></legend>
        <?endif?>
        <div class="pure-g">
            <div class="pure-u-1-2">
                <?= Form::file('photos[]') ?>
                <?= Form::file('photos[]') ?>
                <?= Form::file('photos[]') ?>
            </div>
            <div class="pure-u-1-2">
                <?= Form::file('photos[]') ?>
                <?= Form::file('photos[]') ?>
                <?= Form::file('photos[]') ?>
            </div>
        </div>

        <legend>Контактная информация</legend>
        <?= Form::label('name', 'Имя')?>
        <?= Form::input('name', Arr::get($_POST,'name', $model->name)) ?>

        <?= Form::label('email', 'E-mail')?>
        <?= Form::input('email', Arr::get($_POST,'email', $model->email)) ?>

        <?= Form::label('phone', 'Телефон')?>
        <?= Form::input('phone', Arr::get($_POST,'phone', $model->phone)) ?>

        <?= Form::label('city_id', 'Регион')?>
        <?= Form::select('city_id', $cities, Arr::get($_POST,'city_id', $model->city_id)) ?>

        <?= Form::label('address', 'Адрес')?>
        <?= Form::input('address', Arr::get($_POST,'address', $model->address)) ?>
        <!--        --><?//= Form::input('address', Arr::get($_POST,'address')) ?>
        <br><br>
        <?=Form::submit('update', __('Save ad'), array('class' => 'pure-button pure-button-primary'));  ?>
    </fieldset>
<?=Form::close()?>

<?if($model->loaded()):?>
<script type="text/javascript">
    var modelId = <?php echo $model->id?>;
</script>
<?endif?>