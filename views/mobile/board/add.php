<?php defined('SYSPATH') OR die('No direct script access.');?>
<h1 class="uk-h2">Подать бесплатное объявление на Doreno</h1>
<p class="pure-alert pure-alert-warning">
    <b>Внимание!</b>
    <br> Запрещено подавать объявления с одинаковыми (похожими) заголовками, содержимым и фотографиями.<br />
    Запрещено использовать в тексте и в заголовке объявления ЗАГЛАВНЫЕ буквы.<br />
    <b>Подобные объявления будут удаляться без предупреждения пользователя.</b><br />
    С полными правилами вы можете ознакомиться <a title="Правила" target="_blank" href="http://zxcc.ru/page/terms"><b>здесь</b></a>.
</p>

<?php echo Form::open('', array('class' => 'uk-form uk-form-stacked', 'enctype' => 'multipart/form-data','id'=>'addForm'))?>
<?if(isset($errors)) echo View::factory('mobile/error/validation', array('errors'=>$errors))->render() ?>
    <legend class="uk-margin-top">Заголовок и описание</legend>
    <div class="uk-form-row">
        <?php echo Form::label('title', 'Заголовок', array('class'=>'uk-form-label'))?>
        <?php echo Form::input('title', $model->title, array('class'=>'poshytip'.(isset($errors['title']) ? ' uk-form-danger': ''), 'id'=>'titleInput'))?>
        <p style="display: none;" id="titleInputTip"><b>Введите наименование товара, объекта или услуги.</b><br>В заголовке <b>не допускается: номер телефона, электронный адрес, ссылки</b><br>Так же не допускаются заглавные буквы (кроме аббревиатур).</p>
        <div class="uk-text-muted uk-text-small">Осталось символов: <span id="titleLeft"></span></div>
    </div>

    <div class="uk-form-row">
        <?php echo Form::label('maincategory_id', 'Категория', array('class'=>'uk-form-label'))?>
        <?php echo Form::hidden('maincategory_id', Arr::get($_POST,'maincategory_id'), array('id'=>'mainCategory')) ?>
        <?php echo Form::select('cat_main', $categories_main, Arr::get($_POST,'cat_main'), array('class'=>isset($errors['category_id']) ? 'uk-form-danger': '', 'id'=>'catMain'))?>
        <span id="subCategory"><?php echo $cat_child ?></span>
        <div id="filter_holder"><?php echo $filters ?></div>
    </div>

    <div class="uk-form-row">
        <?php echo Form::label('description', 'Описание', array('class'=>'uk-form-label'))?>
        <?php echo Form::textarea('description', $model->description , array('class'=>'poshytip'.(isset($errors['description']) ? ' uk-form-danger': ''), 'id'=>'textInput', 'title'=>'Укажите подробное описание товара или услуги.<br>Максимально допускается 4096 символов.'))?>
        <p id="textInputTip" style="display: none;"><b>Добавьте описание вашего товара/услуги,</b> укажите преимущества и важные детали.<br>В описании <b>не допускается указание контактных данных.</b><br>Описание должно соответствовать заголовку и предлагаемому товару/услуге.<br>Не допускаются заглавные буквы (кроме аббревиатур).</li></p>
        <div class="uk-text-muted uk-text-small">Осталось символов: <span id="textLeft"></span></div>
    </div>

    <div class="uk-form-row" id="price_holder">
        <label for="option-two" class="uk-form-label" id="eventChangeLabel">
            <?php echo Form::radio('price_type', 1, $model->price_type == 1, array('id'=>'option-two')) ?> <?php echo __('Change')?>
        </label>
        <label for="option-three" class="uk-form-label" id="eventFreeLabel">
            <?php echo Form::radio('price_type', 2, $model->price_type == 2, array('id'=>'option-three')) ?> <?php echo __('For free')?>
        </label>
        <label for="option-one" class="uk-form-label uk-float-left">
            <?php echo Form::radio('price_type', 0, $model->price_type==0, array('id'=>'option-one', 'class'=>'')) ?>
            <span class="" id="eventPriceLabel"><?php echo __('Price')?></span>
            <?php echo Form::input('price', $model->price, array('class'=>(isset($errors['price']) ? 'uk-form-danger ': '').' uk-form-width-small', 'id'=>'eventPrice')) ?>
            <?php echo Form::select('price_unit', $units_options, $model->price_unit) ?>
        </label>
        <label id="trade_styler" class="uk-form-label uk-float-left uk-margin-left"><?php echo Form::hidden('trade', 0) ?><?php echo Form::checkbox('trade', 1, $model->trade==1) ?> <?php echo __('Trade')?></label>
    </div>

    <?/*<div class="uk-form-row">
        <?php echo Form::label('type', 'Тип объявления', array('class'=>'uk-form-label'))?>
        <?php echo Form::select('type', KoMS::translateArray(Model_BoardAd::$adType), $model->type, array('class'=>(isset($errors['type']) ? 'uk-form-danger': ''), 'id'=>'eventType')) ?>
    </div>*/?>

    <legend class="uk-margin-top">Фотографии</legend>
    <div class="uk-column-large-1-3 uk-column-medium-1-2 uk-column-small-1-1" id="photosInput">
            <?php echo Form::file('photos[]') ?>
            <?php echo Form::file('photos[]') ?>
            <?php echo Form::file('photos[]') ?>
            <?php echo Form::file('photos[]') ?>
            <?php echo Form::file('photos[]') ?>
            <?php echo Form::file('photos[]') ?>
    </div>
    <p id="photosInputTip" style="display: none;">
        <b>Запрещены к размещению фотографии с обнаженными людьми и фотографии эротического содержания.</b><br>
        Максимальный размер файла <?php echo ini_get('upload_max_filesize')?>б, формат .jpg, .jpeg, .png, .gif <br>
        Обратите внимание: указание контактной информации на фото не допускается.
    </p>
    <p class="uk-text-muted uk-text-small">Объявления с фото получают в среднем в 3-5 раз больше откликов.</p>

    <legend class="uk-margin-top">Контактная информация</legend>
    <div class="uk-form-row">
    <?php echo Form::label('name', 'Имя', array('class'=>'uk-form-label'))?>
    <?php echo Form::input('name', Arr::get($_POST,'name', $logged ? $user->profile->name : NULL) , array('class'=>'poshytip' . (isset($errors['name']) ? ' uk-form-danger': ''), 'id'=>'nameInput')) ?>
    <p id="nameInputTip" style="display: none;">Как к Вам обращаться?</p>
    </div>

    <div class="uk-form-row">
        <?php echo Form::label('email', 'E-mail', array('class'=>'uk-form-label'))?>
        <?if(Auth::instance()->logged_in()):?>
        <?php echo Form::input('email', Arr::get($_POST,'email', $logged ? $user->email : NULL) , array('disabled'=>'disabled', 'id'=>'emailInput')) ?>
        <?else:?>
        <?php echo Form::input('email', Arr::get($_POST,'email', $logged ? $user->email : NULL) , array('class'=>'poshytip' . (isset($errors['email']) ? ' uk-form-danger': ''), 'id'=>'emailInput')) ?>
        <p id="emailInputTip" style="display: none;">Введите ваш email-адрес<br>Вы будете использовать указанный e-mail для входа на сайт. Допускается одна учетная запись для одного пользователя.</p>
        <?endif?>
    </div>

    <div class="uk-form-row">
        <?php echo Form::label('phone', 'Телефон', array('class'=>'uk-form-label'))?>
        <?php echo Form::input('phone', Arr::get($_POST,'phone', $logged ? $user->profile->phone : NULL) , array('class'=>'poshytip' . (isset($errors['phone']) ? ' uk-form-danger': ''), 'id'=>'phoneInput')) ?>
        <p id="phoneInputTip" style="display: none;">Вы можете ввести несколько номеров, разделив их запятой.</p>
    </div>
    <div class="uk-form-row">
        <?php echo Form::label('site', 'Адрес сайта', array('class'=>'uk-form-label'))?>
        <?php echo Form::input('site', Arr::get($_POST,'site'), array('class'=>'poshytip', 'id'=>'siteInput')) ?>
        <p id="siteInputTip" style="display: none;">Укажите адрес страницы описывающей товар или услугу, если таковая имеется</p>
    </div>

    <div class="uk-form-row">
        <?php echo Form::label('city_id', 'Регион ', array('class'=>'uk-form-label'))?>
        <?php echo Form::hidden('city_id', Arr::get($_POST, 'city_id', $city_id) , array('id'=>'city_id')) ?>
        <?php echo Form::select('region', $regions, $region, array('class'=>isset($errors['city_id']) ? 'uk-form-danger': '', 'id'=>'region'))?>
        <span id="subRegion"><?php echo $cities ?></span>
    </div>

    <div class="uk-form-row">
    <?php echo Form::label('address', 'Адрес', array('class'=>'clear uk-form-label'))?>
    <?php echo Form::input('address', Arr::get($_POST,'address', $logged ? $user->profile->address : NULL) , array('class'=>'poshytip' . (isset($errors['address']) ? ' uk-form-danger': ''), 'id'=>'addressInput')) ?>
    <p id="addressInputTip" style="display: none;">Тут можно указать название населенного пункта, если его нет в списке регионов.<br>А так же район, улицу, станцию метро, почтовый индекс</p>

    <?if(!Auth::instance()->logged_in('login') || Model_BoardAd::checkFrequentlyAdded()):?>
    <div class="uk-form-row">
        <?php echo Captcha::instance(); ?>
    </div>
    <?endif?>
    <div class="uk-margin">
        <?php echo Form::label('termagree', Form::checkbox('termagree', 1, TRUE) . HTML::anchor('/page/terms', __('You\'ve agreed with terms and conditions of ads publication'), array('target'=>'_blank')), array('class'=>'')) ?>
    </div>
    <div class="uk-form-row">
        <?php echo Form::button('update', __('Add ad'), array('class' => 'uk-button uk-button-primary'));  ?>
    </div>
    <div class="uk-clearfix"></div>
<?php echo Form::close()?>

<script type="text/javascript">
var job_ids = <?php echo json_encode($job_ids)?>;
var noprice_ids = <?php echo json_encode($noprice_ids)?>;
</script>