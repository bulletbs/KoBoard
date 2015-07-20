<?php defined('SYSPATH') OR die('No direct script access.');?>

<h1><?php echo __('Add ad')?></h1>

<?=Form::open('', array('class' => 'pure-form  pure-form-stacked', 'enctype' => 'multipart/form-data','id'=>'addForm'))?>
    <?if(isset($errors)) echo View::factory('error/validation', array('errors'=>$errors))->render() ?>
    <fieldset>
        <legend>Подать бесплатное объявление</legend>
        <?= Form::label('title', 'Заголовок')?>
        <?= Form::input('title', $model->title, array('class'=>isset($errors['title']) ? 'error-input': ''))?>

        <?= Form::label('maincategory_id', 'Категория')?>
        <?= Form::hidden('maincategory_id', Arr::get($_POST,'maincategory_id'), array('id'=>'mainCategory')) ?>
        <?= Form::select('cat_main', $categories_main, Arr::get($_POST,'cat_main'), array('class'=>isset($errors['category_id']) ? 'error-input': '', 'id'=>'catMain'))?>
        <span id="subCategory"><?= $cat_child ?></span>
        <div id="filter_holder"><?= $filters ?></div>

        <?= Form::label('description', 'Описание')?>
        <?= Form::textarea('description', $model->description , array('class'=>isset($errors['description']) ? 'error-input': ''))?>

        <?= Form::label('price', 'Цена ('.$price_value.')', array('id'=>'eventPriceLabel'))?>
        <?= Form::input('price', $model->price, array('class'=>(isset($errors['price']) ? 'error-input': ''), 'id'=>'eventPrice')) ?>

        <?= Form::label('type', 'Тип объявления')?>
        <?= Form::select('type', KoMS::translateArray(Model_BoardAd::$adType), $model->type, array('class'=>(isset($errors['type']) ? 'error-input': ''), 'id'=>'eventType')) ?>

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
        <?= Form::input('name', Arr::get($_POST,'name', $logged ? $user->profile->name : NULL) , array('class'=>isset($errors['name']) ? 'error-input': '')) ?>

        <?= Form::label('email', 'E-mail')?>
        <?= Form::input('email', Arr::get($_POST,'email', $logged ? $user->email : NULL) , array('class'=>isset($errors['email']) ? 'error-input': '')) ?>

        <?= Form::label('phone', 'Телефон')?>
        <?= Form::input('phone', Arr::get($_POST,'phone', $logged ? $user->profile->phone : NULL) , array('class'=>isset($errors['phone']) ? 'error-input': '')) ?>
<!--        --><?//= Form::input('phone', Arr::get($_POST,'phone')) ?>

        <?= Form::label('city_id', 'Регион')?>
        <?= Form::hidden('city_id', Arr::get($_POST,'city_id') , array('id'=>'city_id')) ?>
        <?= Form::select('region', $regions, Arr::get($_POST,'region'), array('class'=>isset($errors['city_id']) ? 'error-input': '', 'id'=>'region'))?>
        <span id="subRegion"><?= $cities ?></span>

        <?= Form::label('address', 'Адрес', array('class'=>'clear'))?>
        <?= Form::input('address', Arr::get($_POST,'address', $logged ? $user->profile->address : NULL) , array('class'=>isset($errors['address']) ? 'error-input': '')) ?>
        <br><br>
        <?=Form::submit('update', __('Add ad'), array('class' => 'pure-button pure-button-primary'));  ?>
    </fieldset>
<?=Form::close()?>

<script type="text/javascript">var job_ids = <?php echo json_encode($job_ids)?>;</script>