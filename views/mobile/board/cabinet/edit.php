<?php defined('SYSPATH') or die('No direct script access.');?>
<h1 class="uk-h2"><?php echo __('Ad edit')?></h1>
<?if(!$model->loaded()):?>
<p class="uk-alert uk-alert-warning">
    <b>Внимание!</b>
    <br> Запрещено подавать объявления с одинаковыми (похожими) заголовками, содержимым и фотографиями.<br />
    Запрещено использовать в тексте и в заголовке объявления ЗАГЛАВНЫЕ буквы.<br />
    <b>Подобные объявления будут удаляться без предупреждения пользователя.</b><br />
    С полными правилами вы можете ознакомиться <a title="Правила" href="http://doreno.ru/page/terms"><b>тут</b></a>.
</p>
<?endif?>
<?=Form::open('', array('class' => 'uk-form  uk-form-stacked', 'enctype' => 'multipart/form-data','id'=>'addForm'))?>
<?if(isset($errors)) echo View::factory('mobile/error/validation', array('errors'=>$errors))->render()?>
<legend class="uk-margin-top">Заголовок и описание</legend>
    <div class="uk-form-row">
    <?= Form::label('title', 'Заголовок', array('class'=>'uk-form-label'))?>
        <?= Form::input('title', Arr::get($_POST,'title', $model->title), array('class'=>'poshytip'.(isset($errors['title']) ? ' uk-form-danger': ''), 'id'=>'titleInput'))?>
        <p style="display: none;" id="titleInputTip"><b>Введите наименование товара, объекта или услуги.</b><br>В заголовке <b>не допускается: номер телефона, электронный адрес, ссылки</b><br>Так же не допускаются заглавные буквы (кроме аббревиатур).</p>
        <div class="uk-text-muted uk-text-small">Осталось символов: <span id="titleLeft"></span></div>
    </div>

    <div class="uk-form-row">
        <?= Form::label('category_id', 'Категория', array('class'=>'uk-form-label'))?>
        <?= Form::hidden('category_id', Arr::get($_POST,'category_id', $model->category_id), array('id'=>'mainCategory')) ?>
        <?= Form::select('cat_main', $categories_main, Arr::get($_POST,'cat_main', $model->pcategory_id), array('class'=>isset($errors['category_id']) ? 'uk-form-danger': '', 'id'=>'catMain'))?>
        <span id="subCategory"><?= $cat_child ?></span>
        <div id="filter_holder"><?= $filters ?></div>
    </div>


    <div class="uk-form-row">
        <?= Form::label('description', 'Описание', array('class'=>'uk-form-label'))?>
        <?= Form::textarea('description', Arr::get($_POST,'description', $model->description), array('class'=>'poshytip'.(isset($errors['description']) ? ' uk-form-danger': ''), 'id'=>'textInput'))?>
        <p id="textInputTip" style="display: none;"><b>Добавьте описание вашего товара/услуги,</b> укажите преимущества и важные детали.<br>В описании <b>не допускается указание контактных данных.</b><br>Описание должно соответствовать заголовку и предлагаемому товару/услуге.<br>Не допускаются заглавные буквы (кроме аббревиатур).</li></p>
        <div class="uk-text-muted uk-text-small">Осталось символов: <span id="textLeft"></span></div>
    </div>

    <div class="uk-form-row">
        <?= Form::label('type', 'Тип объявления', array('class'=>'uk-form-label'))?>
        <?= Form::select('type', KoMS::translateArray(Model_BoardAd::$adType), $model->type, array('class'=>(isset($errors['type']) ? 'uk-form-danger': ''), 'id'=>'eventType')) ?>
    </div>

    <div class="uk-form-row" id="price_holder">
        <label for="option-two" class="uk-form-label" id="eventChangeLabel">
            <?= Form::radio('price_type', 1, $model->price_type == 1, array('id'=>'option-two')) ?> <?php echo __('Change')?>
        </label>
        <label for="option-three" class="uk-form-label" id="eventFreeLabel">
            <?= Form::radio('price_type', 2, $model->price_type == 2, array('id'=>'option-three')) ?> <?php echo __('For free')?>
        </label>
        <label for="option-one" class="uk-form-label uk-float-left">
            <?= Form::radio('price_type', 0, $model->price_type==0, array('id'=>'option-one', 'class'=>'')) ?>
            <span class="" id="eventPriceLabel"><?php echo __('Price')?></span>
            <?= Form::input('price', $model->price, array('class'=>(isset($errors['price']) ? 'uk-form-danger ': '').'', 'id'=>'eventPrice')) ?>
            <?= Form::select('price_unit', $units_options, $model->price_unit, array('class'=>'')) ?>
        </label>
        <label class="uk-form-label uk-float-left uk-margin-left" id="trade_styler"><?= Form::hidden('trade', 0) ?><?= Form::checkbox('trade', 1, $model->trade==1) ?> <?php echo __('Trade')?></label>
    </div>

    <legend>Фотографии</legend>
    <?if(count($photos)):?>
        <div class="uk-column-large-1-6 uk-column-medium-1-4 uk-column-small-1-2" id="photosInput">
        <?foreach($photos as $photo):?>
            <div style="display: inline-block;">
                <?= HTML::anchor($photo->getPhotoUri(), $photo->getThumbTag('',array('class'=>'thumbnail')), array('target'=>'_blank')) ?><br>
                <?= FORM::checkbox('delphotos[]', $photo->id, FALSE)?> удалить<br>
                <?= FORM::radio('setmain', $photo->id, $photo->main == 1)?> основная
            </div><?endforeach;?>
        </div>
        <hr class="uk-divider">
    <?endif?>

    <div class="uk-column-large-1-3 uk-column-medium-1-2 uk-column-small-1-1" id="photosInput">
        <?= Form::file('photos[]') ?>
        <?= Form::file('photos[]') ?>
        <?= Form::file('photos[]') ?>
        <?= Form::file('photos[]') ?>
        <?= Form::file('photos[]') ?>
        <?= Form::file('photos[]') ?>
    </div>
    <p id="photosInputTip" style="display: none;">
        <b>Запрещены к размещению фотографии с обнаженными людьми и фотографии эротического содержания.</b><br>
        Максимальный размер файла <?php echo ini_get('upload_max_filesize')?>б, формат .jpg, .jpeg, .png, .gif <br>
        Обратите внимание: указание контактной информации на фото не допускается.
    </p>
    <p class="uk-text-muted uk-text-small">Объявления с фото получают в среднем в 3-5 раз больше откликов.</p>

    <div class="uk-form-row">
        <legend>Контактная информация</legend>
        <?= Form::label('name', 'Имя', array('class'=>'uk-form-label'))?>
        <?= Form::input('name', Arr::get($_POST,'name', $model->name), array('id'=>'nameInput', 'class'=>'poshytip')) ?>
    <p id="nameInputTip" style="display: none;">Как к Вам обращаться?</p>

    <div class="uk-form-row">
        <?= Form::label('email', 'E-mail', array('class'=>'uk-form-label'))?>
        <?= Form::input('email', Arr::get($_POST,'email', $model->email), array('disabled'=>'disabled')) ?>
    </div>

    <div class="uk-form-row"><?= Form::label('phone', 'Телефон', array('class'=>'uk-form-label'))?>
        <?= Form::input('phone', Arr::get($_POST,'phone', $model->phone), array('id'=>'phoneInput', 'class'=>'poshytip')) ?>
        <p id="phoneInputTip" style="display: none;">Вы можете ввести несколько номеров, разделив их запятой.</p>
    </div>

    <div class="uk-form-row"><?= Form::label('site', 'Адрес сайта', array('class'=>'uk-form-label'))?>
        <?= Form::input('site', Arr::get($_POST,'site', $model->site), array('id'=>'siteInput', 'class'=>'poshytip')) ?>
        <p id="siteInputTip" style="display: none;">Укажите адрес страницы описывающей товар или услугу, если таковая имеется</p>
    </div>

    <div class="uk-form-row"><?= Form::label('city_id', 'Регион', array('class'=>'uk-form-label'))?>
        <?= Form::hidden('city_id', Arr::get($_POST,'city_id', $model->city_id) , array('id'=>'city_id')) ?>
        <?= Form::select('region', $regions, Arr::get($_POST,'region', $model->pcity_id), array(isset($errors['city_id']) ? 'uk-form-danger': '', 'id'=>'region'))?>
        <span id="subRegion"><?= $cities ?></span>
    </div>

    <div class="uk-form-row"><?= Form::label('address', 'Адрес', array('class'=>'clear uk-form-label'))?>
        <?= Form::input('address', Arr::get($_POST,'address', $model->address), array('id'=>'addressInput', 'class'=>'poshytip')) ?>
        <p id="addressInputTip" style="display: none;">Тут можно указать название населенного пункта, если его нет в списке регионов.<br>А так же район, улицу, станцию метро, почтовый индекс</p>
    </div>

    <div class="uk-margin">
        <?if(!$model->loaded()):?><?= Form::label('termagree', Form::checkbox('termagree', 1, TRUE) . HTML::anchor('/page/terms', __('You\'ve agreed with terms and conditions of ads publication'), array('target'=>'_blank')), array('class'=>'left')) ?><?endif?>
    </div>
    <div class="uk-form-row">
        <?=Form::button('update', __('Save ad'), array('class' => 'uk-button uk-button-primary'));  ?>
    </div>
<?=Form::close()?>

<script type="text/javascript">
    <?if($model->loaded()):?>
    var job_ids = <?php echo json_encode($job_ids)?>;
    var noprice_ids = <?php echo json_encode($noprice_ids)?>;
    var modelId = <?php echo $model->id?>;
    <?endif?>
</script>