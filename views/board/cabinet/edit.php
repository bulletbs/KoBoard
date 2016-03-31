<?php defined('SYSPATH') or die('No direct script access.');?>
<h1><?php echo $model->loaded() ? __('Ad edit') : __('Add ad') ?></h1>
<?if(!$model->loaded()):?>
<p class="pure-alert pure-alert-warning">
    <b>Внимание!</b>
    <br> Запрещено подавать объявления с одинаковыми (похожими) заголовками, содержимым и фотографиями.<br />
    Запрещено использовать в тексте и в заголовке объявления ЗАГЛАВНЫЕ буквы.<br />
    <b>Подобные объявления будут удаляться без предупреждения пользователя.</b><br />
    С полными правилами вы можете ознакомиться <a title="Правила" target="_blank" href="http://zxcc.ru/page/terms"><b>здесь</b></a>.
</p>
<?if(Auth::instance()->logged_in('company')):?><div class="pure-alert">Для магазинов, <b>все новые объявления автоматически получают тип "Бизнес"</b> и привязываются к вашему магазину.</div><?endif;?>
<?endif?>
<?=Form::open('', array('class' => 'pure-form  pure-form-stacked', 'enctype' => 'multipart/form-data','id'=>'addForm'))?>
<?if(isset($errors)) echo View::factory('error/validation', array('errors'=>$errors))->render()?>
    <fieldset>
        <legend>Описание объявления</legend>
        <?= Form::label('title', 'Заголовок')?>
        <?= Form::input('title', Arr::get($_POST,'title', $model->title), array('class'=>'poshytip'.(isset($errors['title']) ? ' error-input': ''), 'id'=>'titleInput'))?>
        <p style="display: none;" id="titleInputTip"><b>Введите наименование товара, объекта или услуги.</b><br>В заголовке <b>не допускается: номер телефона, электронный адрес, ссылки</b><br>Так же не допускаются заглавные буквы (кроме аббревиатур).</p>
        <div class="quiet">Осталось символов: <span id="titleLeft"></span></div><br>

        <?= Form::label('category_id', 'Категория')?>
        <?= Form::hidden('category_id', Arr::get($_POST,'category_id', $model->category_id), array('id'=>'mainCategory')) ?>
        <?= Form::select('cat_main', $categories_main, Arr::get($_POST,'cat_main', $model->pcategory_id), array('class'=>isset($errors['category_id']) ? 'error-input': '', 'id'=>'catMain'))?>
        <span id="subCategory"><?= $cat_child ?></span>
        <div id="filter_holder"><?= $filters ?></div>

        <?= Form::label('description', 'Описание')?>
        <?= Form::textarea('description', Arr::get($_POST,'description', $model->description), array('class'=>'poshytip'.(isset($errors['description']) ? ' error-input': ''), 'id'=>'textInput'))?>
        <p id="textInputTip" style="display: none;"><b>Добавьте описание вашего товара/услуги,</b> укажите преимущества и важные детали.<br>В описании <b>не допускается указание контактных данных.</b><br>Описание должно соответствовать заголовку и предлагаемому товару/услуге.<br>Не допускаются заглавные буквы (кроме аббревиатур).</li></p>
        <div class="quiet">Осталось символов: <span id="textLeft"></span></div><br>

<!--        --><?//= Form::label('type', 'Тип объявления')?>
<!--        --><?//= Form::select('type', KoMS::translateArray(Model_BoardAd::$adType), $model->type, array('class'=>(isset($errors['type']) ? 'error-input': ''), 'id'=>'eventType')) ?>

        <div id="price_holder">
            <div class="hspacer_10"></div>
            <label for="option-two" class="pure-radio clear" id="eventChangeLabel">
                <?= Form::radio('price_type', 1, $model->price_type == 1, array('id'=>'option-two')) ?> <?php echo __('Change')?>
            </label>
            <label for="option-three" class="pure-radio" id="eventFreeLabel">
                <?= Form::radio('price_type', 2, $model->price_type == 2, array('id'=>'option-three')) ?> <?php echo __('For free')?>
            </label>
            <label for="option-one" class="pure-radio">
                <?= Form::radio('price_type', 0, $model->price_type==0, array('id'=>'option-one', 'class'=>'left')) ?>
                <span class="price_label" id="eventPriceLabel"><?php echo __('Price')?></span>
                <?= Form::input('price', $model->price, array('class'=>(isset($errors['price']) ? 'error-input ': '').' left', 'id'=>'eventPrice')) ?>
                <?= Form::select('price_unit', $units_options, $model->price_unit, array('class'=>'left')) ?>
            </label>
            <label id="trade_styler"><?= Form::hidden('trade', 0) ?><?= Form::checkbox('trade', 1, $model->trade==1) ?> <?php echo __('Trade')?></label>
            <div class="hspacer_10"></div>
        </div>

        <legend>Фотографии</legend>
        <?if(count($photos)):?>
            <?foreach($photos as $photo):?>
                <div class="pure-u-5-24">
                    <?= HTML::anchor($photo->getPhotoUri(), $photo->getThumbTag('',array('class'=>'thumbnail')), array('target'=>'_blank')) ?><br>
                    <?= FORM::checkbox('delphotos[]', $photo->id, FALSE)?> удалить<br>
                    <?= FORM::radio('setmain', $photo->id, $photo->main == 1)?> основная
                </div>
            <?endforeach;?>
            <legend></legend>
        <?endif?>
        <br /><br />

        <div class="pure-g poshytip" id="photosInput">
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
        <p id="photosInputTip" style="display: none;">
            <b>Запрещены к размещению фотографии с обнаженными людьми и фотографии эротического содержания.</b><br>
            Максимальный размер файла <?php echo ini_get('upload_max_filesize')?>б, формат .jpg, .jpeg, .png, .gif <br>
            Обратите внимание: указание контактной информации на фото не допускается.
        </p>
        Объявления с фото получают в среднем в 3-5 раз больше откликов.

        <legend>Контактная информация</legend>
        <?= Form::label('name', 'Имя')?>
        <?= Form::input('name', Arr::get($_POST,'name', $model->name), array('id'=>'nameInput', 'class'=>'poshytip')) ?>
        <p id="nameInputTip" style="display: none;">Как к Вам обращаться?</p>

        <?= Form::label('email', 'E-mail')?>
        <?= Form::input('email', Arr::get($_POST,'email', $model->email), array('disabled'=>'disabled')) ?>

        <?= Form::label('phone', 'Телефон')?>
        <?= Form::input('phone', Arr::get($_POST,'phone', $model->phone), array('id'=>'phoneInput', 'class'=>'poshytip')) ?>
        <p id="phoneInputTip" style="display: none;">Вы можете ввести несколько номеров, разделив их запятой.</p>

        <?= Form::label('site', 'Адрес сайта')?>
        <?= Form::input('site', Arr::get($_POST,'site', $model->site), array('id'=>'siteInput', 'class'=>'poshytip')) ?>
        <p id="siteInputTip" style="display: none;">Укажите адрес страницы описывающей товар или услугу, если таковая имеется</p>

        <?= Form::label('city_id', 'Регион')?>
        <?= Form::hidden('city_id', Arr::get($_POST,'city_id', $model->city_id) , array('id'=>'city_id')) ?>
        <?= Form::select('region', $regions, Arr::get($_POST,'region', $model->pcity_id), array('class'=>isset($errors['city_id']) ? 'error-input': '', 'id'=>'region'))?>
        <span id="subRegion"><?= $cities ?></span>

        <?= Form::label('address', 'Адрес', array('class'=>'clear'))?>
        <?= Form::input('address', Arr::get($_POST,'address', $model->address), array('id'=>'addressInput', 'class'=>'poshytip')) ?>
        <p id="addressInputTip" style="display: none;">Тут можно указать название населенного пункта, если его нет в списке регионов.<br>А так же район, улицу, станцию метро, почтовый индекс</p>

        <?if(!$model->loaded() && Model_BoardAd::checkFrequentlyAdded()):?>
            <Br><?php echo Captcha::instance() ?>
            <?= Form::label('captcha', __('Enter captcha code')) ?>
            <?php echo Form::input('captcha', NULL, array('class'=>isset($errors['_external']['captcha']) ? 'error-input': '', 'id' => 'captcha-key'))?>
        <?endif?>
        <br><br>
        <?=Form::submit('update', __('Save ad'), array('class' => 'pure-button pure-button-primary left'));  ?>
        <?if(!$model->loaded()):?><?= Form::label('termagree', Form::checkbox('termagree', 1, TRUE) . HTML::anchor('/page/terms', __('You\'ve agreed with terms and conditions of ads publication'), array('target'=>'_blank')), array('class'=>'left')) ?><?endif?>
    </fieldset>
<?=Form::close()?>

<script type="text/javascript">
    <?if($model->loaded()):?>
    var job_ids = <?php echo json_encode($job_ids)?>;
    var noprice_ids = <?php echo json_encode($noprice_ids)?>;
    var modelId = <?php echo $model->id?>;
    <?endif?>
</script>