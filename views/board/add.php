<?php defined('SYSPATH') OR die('No direct script access.');?>
<h1>Подать бесплатное объявление на <?php echo KoMS::config()->project['name'] ?></h1>
<br />
<p class="pure-alert pure-alert-warning">
    <b>Внимание!</b>
    <br> Запрещено подавать объявления с одинаковыми (похожими) заголовками, содержимым и фотографиями.<br />
    Запрещено использовать в тексте и в заголовке объявления ЗАГЛАВНЫЕ буквы и восклицательные знаки (!).<br />
    <b>Подобные объявления будут удаляться без предупреждения пользователя.</b><br />
    С полными правилами вы можете ознакомиться <a title="Правила" target="_blank" href="http://zxcc.ru/page/terms"><b>здесь</b></a>.
</p>

<?php echo Form::open('', array('class' => 'pure-form  pure-form-stacked', 'enctype' => 'multipart/form-data','id'=>'addForm'))?>
    <?if(isset($errors)) echo View::factory('error/validation', array('errors'=>$errors))->render() ?>
    <fieldset>
        <legend>Заголовок и описание</legend>
        <?php echo Form::label('title', 'Заголовок')?>
        <?php echo Form::input('title', $model->title, array('class'=>'poshytip'.(isset($errors['title']) ? ' error-input': ''), 'id'=>'titleInput'))?>
        <p style="display: none;" id="titleInputTip"><b>Введите наименование товара, объекта или услуги.</b><br>В заголовке <b>не допускается: номер телефона, электронный адрес, ссылки</b><br>Так же не допускаются заглавные буквы (кроме аббревиатур).</p>
        <div class="quiet">Осталось символов: <span id="titleLeft"></span></div><br>

        <?php echo Form::label('maincategory_id', 'Категория')?>
        <?php echo Form::hidden('maincategory_id', Arr::get($_POST,'maincategory_id'), array('id'=>'mainCategory')) ?>
        <?php echo Form::select('cat_main', $categories_main, Arr::get($_POST,'cat_main'), array('class'=>isset($errors['category_id']) ? 'error-input': '', 'id'=>'catMain'))?>
        <span id="subCategory"><?php echo $cat_child ?></span>
        <div id="filter_holder"><?php echo $filters ?></div>

        <?php echo Form::label('description', 'Описание')?>
        <?php echo Form::textarea('description', $model->description , array('class'=>'poshytip'.(isset($errors['description']) ? ' error-input': ''), 'id'=>'textInput', 'title'=>'Укажите подробное описание товара или услуги.<br>Максимально допускается 4096 символов.'))?>
        <p id="textInputTip" style="display: none;"><b>Добавьте описание вашего товара/услуги,</b> укажите преимущества и важные детали.<br>В описании <b>не допускается указание контактных данных.</b><br>Описание должно соответствовать заголовку и предлагаемому товару/услуге.<br>Не допускаются заглавные буквы (кроме аббревиатур).</li></p>
        <div class="quiet">Осталось символов: <span id="textLeft"></span></div><br>

        <div id="price_holder">
            <div class="hspacer_10"></div>
            <label for="option-two" class="pure-radio clear" id="eventChangeLabel">
                <?php echo Form::radio('price_type', 1, $model->price_type == 1, array('id'=>'option-two')) ?> <?php echo __('Change')?>
            </label>
            <label for="option-three" class="pure-radio" id="eventFreeLabel">
                <?php echo Form::radio('price_type', 2, $model->price_type == 2, array('id'=>'option-three')) ?> <?php echo __('For free')?>
            </label>
            <label for="option-one" class="pure-radio">
                <?php echo Form::radio('price_type', 0, $model->price_type==0, array('id'=>'option-one', 'class'=>'left')) ?>
                <span class="price_label" id="eventPriceLabel"><?php echo __('Price')?></span>
                <?php echo Form::input('price', $model->price, array('class'=>(isset($errors['price']) ? 'error-input ': '').' left', 'id'=>'eventPrice')) ?>
                <?php echo Form::select('price_unit', $units_options, $model->price_unit, array('class'=>'left')) ?>
            </label>
            <label id="trade_styler"><?php echo Form::hidden('trade', 0) ?><?php echo Form::checkbox('trade', 1, $model->trade==1) ?> <?php echo __('Trade')?></label>
            <div class="hspacer_10"></div>
        </div>

        <div id="jobType" style="display: none;">
            <?php echo Form::label('type', 'Тип предложения')?>
            <?php echo Form::select('type', KoMS::translateArray(Model_BoardAd::$adType), $model->type, array('class'=>(isset($errors['type']) ? 'error-input': ''), 'id'=>'eventType')) ?>?>
        </div>
        <?/*<?php echo Form::label('type', 'Тип объявления')?>
        <?php echo Form::select('type', KoMS::translateArray(Model_BoardAd::$adType), $model->type, array('class'=>(isset($errors['type']) ? 'error-input': ''), 'id'=>'eventType')) ?>*/?>
    </fieldset>
    <fieldset>
        <legend>Фотографии</legend>
<strong>
</strong><br />

        <div class="pure-g" id="photosInput">
            <div class="pure-u-1-2">
                <?php echo Form::file('photos[]') ?>
                <?php echo Form::file('photos[]') ?>
                <?php echo Form::file('photos[]') ?>
            </div>
            <div class="pure-u-1-2">
                <?php echo Form::file('photos[]') ?>
                <?php echo Form::file('photos[]') ?>
                <?php echo Form::file('photos[]') ?>
            </div>
        </div>
        <p id="photosInputTip" style="display: none;">
            <b>Запрещены к размещению фотографии с обнаженными людьми и фотографии эротического содержания.</b><br>
            Максимальный размер файла <?php echo ini_get('upload_max_filesize')?>б, формат .jpg, .jpeg, .png, .gif <br>
            Обратите внимание: указание контактной информации на фото не допускается.
        </p>
        Объявления с фото получают в среднем в 3-5 раз больше откликов.
    </fieldset>
    <fieldset>
        <legend>Контактная информация</legend>
        <?php echo Form::label('name', 'Имя')?>
        <?php echo Form::input('name', Arr::get($_POST,'name', $logged ? $user->profile->name : NULL) , array('class'=>'poshytip' . (isset($errors['name']) ? ' error-input': ''), 'id'=>'nameInput')) ?>
        <p id="nameInputTip" style="display: none;">Как к Вам обращаться?</p>

        <?php echo Form::label('email', 'E-mail')?>
        <?if(Auth::instance()->logged_in()):?>
        <?php echo Form::input('email', Arr::get($_POST,'email', $logged ? $user->email : NULL) , array('disabled'=>'disabled', 'id'=>'emailInput')) ?>
        <?else:?>
        <?php echo Form::input('email', Arr::get($_POST,'email', $logged ? $user->email : NULL) , array('class'=>'poshytip' . (isset($errors['email']) ? ' error-input': ''), 'id'=>'emailInput')) ?>
        <p id="emailInputTip" style="display: none;">Введите ваш email-адрес<br>Вы будете использовать указанный e-mail для входа на сайт. Допускается одна учетная запись для одного пользователя.</p>
        <?endif?>

        <?php echo Form::label('phone', 'Телефон')?>
        <?php echo Form::input('phone', Arr::get($_POST,'phone', $logged ? $user->profile->phone : NULL) , array('class'=>'poshytip' . (isset($errors['phone']) ? ' error-input': ''), 'id'=>'phoneInput')) ?>
        <p id="phoneInputTip" style="display: none;">Вы можете ввести несколько номеров, разделив их запятой.</p>

        <?php echo Form::label('site', 'Адрес сайта')?>
        <?php echo Form::input('site', Arr::get($_POST,'site'), array('class'=>'poshytip', 'id'=>'siteInput')) ?>
        <p id="siteInputTip" style="display: none;">Укажите адрес страницы описывающей товар или услугу, если таковая имеется</p>

        <?php echo Form::label('city_id', 'Регион ')?>
        <?php echo Form::hidden('city_id', Arr::get($_POST, 'city_id', $city_id) , array('id'=>'city_id')) ?>
        <?php echo Form::select('region', $regions, $region, array('class'=>isset($errors['city_id']) ? 'error-input': '', 'id'=>'region'))?>
        <span id="subRegion"><?php echo $cities ?></span>

        <?php echo Form::label('address', 'Адрес', array('class'=>'clear'))?>
        <?php echo Form::input('address', Arr::get($_POST,'address', $logged ? $user->profile->address : NULL) , array('class'=>'poshytip' . (isset($errors['address']) ? ' error-input': ''), 'id'=>'addressInput')) ?>
        <p id="addressInputTip" style="display: none;">Тут можно указать название населенного пункта, если его нет в списке регионов.<br>А так же район, улицу, станцию метро, почтовый индекс</p>
        <a href="#" rel="<?php echo BoardConfig::instance()->country_name ?>" id="toggleMap" class="pure-button">Показать на карте</a>
        <script language="javascript" src="https://api-maps.yandex.ru/2.0/?load=package.full&amp;lang=ru-RU"></script>
        <p id="addressInputTip" style="display: none;">Тут можно указать название населенного пункта, если его нет в списке регионов.<br>А так же район, улицу, станцию метро, почтовый индекс</p>
        <div class="showAddress" id="showAddress"></div>
        <?if(!Auth::instance()->logged_in('login') || Model_BoardAd::checkFrequentlyAdded()):?>
            <?php echo Captcha::instance(); ?>
        <?endif?>
        <?php echo Form::label('termagree', Form::checkbox('termagree', 1, TRUE) . HTML::anchor('/page/terms', __('You\'ve agreed with terms and conditions of ads publication'), array('target'=>'_blank'))) ?><br>
        <?php echo Form::submit('update', __('Add ad'), array('class' => 'pure-button pure-button-primary left'));  ?>
    </fieldset>
<?php echo Form::close()?>

<script type="text/javascript">
var job_ids = <?php echo json_encode($job_ids)?>;
var noprice_ids = <?php echo json_encode($noprice_ids)?>;
</script>