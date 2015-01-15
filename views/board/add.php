<?php defined('SYSPATH') OR die('No direct script access.');?>

<h1><?php echo __('Add ad')?></h1>

<?=Form::open('', array('class' => 'pure-form  pure-form-stacked', 'enctype' => 'multipart/form-data','id'=>'addForm'))?>
    <?if(isset($errors)) echo View::factory('error/validation', array('errors'=>$errors))->render()?>
    <fieldset>
        <legend>Подать бесплатное объявление</legend>
        <?= Form::label('title', 'Заголовок')?>
        <?= Form::input('title', $model->title, array('class'=>isset($errors['title']) ? 'error-input': ''))?>

        <?= Form::label('maincategory_id', 'Категория')?>
        <?= Form::select('maincategory_id', $categories, Arr::get($_POST,'maincategory_id'), array('class'=>isset($errors['category_id']) ? 'error-input': '', 'id'=>'mainCategory')) ?>
        <div id="subcategories_3" class="subcat_holder"><?= $subcategories ?></div>
        <div id="filter_holder"><?= $filters ?></div>

        <?= Form::label('description', 'Описание')?>
        <?= Form::textarea('description', $model->description , array('class'=>isset($errors['description']) ? 'error-input': ''))?>

        <?= Form::label('price', 'Цена ('.$price_value.')')?>
        <?= Form::input('price', $model->price, array('class'=>(isset($errors['price']) ? 'error-input': ''))) ?>

        <legend>Фотографии</legend>
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
        <?= Form::input('name', Arr::get($_POST,'name', $user->profile->name) , array('class'=>isset($errors['name']) ? 'error-input': '')) ?>

        <?= Form::label('email', 'E-mail')?>
        <?= Form::input('email', Arr::get($_POST,'email', $user->email) , array('class'=>isset($errors['email']) ? 'error-input': '')) ?>

        <?= Form::label('phone', 'Телефон')?>
        <?= Form::input('phone', Arr::get($_POST,'phone', $user->profile->phone) , array('class'=>isset($errors['phone']) ? 'error-input': '')) ?>
<!--        --><?//= Form::input('phone', Arr::get($_POST,'phone')) ?>

        <?= Form::label('city_id', 'Регион')?>
        <?//= Form::select('city_id', $cities, Arr::get($_POST,'city_id', $user->city_id)) ?>
        <?= Form::select('city_id', $cities, Arr::get($_POST,'city_id') , array('class'=>isset($errors['city_id']) ? 'error-input': '')) ?>

        <?= Form::label('address', 'Адрес')?>
        <?= Form::input('address', Arr::get($_POST,'address', $user->profile->address) , array('class'=>isset($errors['address']) ? 'error-input': '')) ?>
<!--        --><?//= Form::input('address', Arr::get($_POST,'address')) ?>
        <br><br>
        <?=Form::submit('update', __('Add ad'), array('class' => 'pure-button pure-button-primary'));  ?>
    </fieldset>
<?=Form::close()?>