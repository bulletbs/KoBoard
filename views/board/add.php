<?php defined('SYSPATH') OR die('No direct script access.');?>
<h1>Подать бесплатное объявление на ZXCC.RU</h1>
<br />
<p style="background-color: #fffacc; padding:10px;">
<b>Внимание!</b>
<br> Запрещено подавать объявления с одинаковыми (похожими) заголовками, содержимым и фотографиями.<br />
Запрещено использовать в тексте и в заголовке объявления ЗАГЛАВНЫЕ буквы.<br />
<b>Подобные объявления будут удаляться без предупреждения пользователя.</b><br />
С полными правилами вы можете ознакомиться <a title="Правила" href="http://zxcc.ru/page/terms"><b>тут</b></a>.
</p>

<?=Form::open('', array('class' => 'pure-form  pure-form-stacked', 'enctype' => 'multipart/form-data','id'=>'addForm'))?>
    <?if(isset($errors)) echo View::factory('error/validation', array('errors'=>$errors))->render() ?>
    <fieldset>
        <legend>Заголовок и описание</legend>
        <?= Form::label('title', 'Заголовок')?>
        <?= Form::input('title', $model->title, array('class'=>isset($errors['title']) ? 'error-input': '', 'id'=>'titleInput'))?>
        <div class="quiet">Осталось символов: <span id="titleLeft"></span></div><br>

        <?= Form::label('maincategory_id', 'Категория')?>
        <?= Form::hidden('maincategory_id', Arr::get($_POST,'maincategory_id'), array('id'=>'mainCategory')) ?>
        <?= Form::select('cat_main', $categories_main, Arr::get($_POST,'cat_main'), array('class'=>isset($errors['category_id']) ? 'error-input': '', 'id'=>'catMain'))?>
        <span id="subCategory"><?= $cat_child ?></span>
        <div id="filter_holder"><?= $filters ?></div>

        <?= Form::label('description', 'Описание')?>
        <?= Form::textarea('description', $model->description , array('class'=>isset($errors['description']) ? 'error-input': '', 'id'=>'textInput'))?>
        <div class="quiet">Осталось символов: <span id="textLeft"></span></div><br>

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

        <?= Form::label('type', 'Тип объявления')?>
        <?= Form::select('type', KoMS::translateArray(Model_BoardAd::$adType), $model->type, array('class'=>(isset($errors['type']) ? 'error-input': ''), 'id'=>'eventType')) ?>
    </fieldset>
    <fieldset>
        <legend>Фотографии</legend>
<strong>Запрещены к размещению фотографии с обнаженными людьми и фотографии эротического содержания.
</strong><br />
Добавить новое изображение (gif, jpg). Рекомендуемый размер - не более <?php echo ini_get('upload_max_filesize')?>б.
<br /><br />

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
Объявления с фото получают в среднем в 3-5 раз больше откликов.
    </fieldset>
    <fieldset>
        <legend>Контактная информация</legend>
        <?= Form::label('name', 'Имя')?>
        <?= Form::input('name', Arr::get($_POST,'name', $logged ? $user->profile->name : NULL) , array('class'=>isset($errors['name']) ? 'error-input': '')) ?>

        <?= Form::label('email', 'E-mail')?>
        <?if(Auth::instance()->logged_in()):?>
        <?= Form::input('email', Arr::get($_POST,'email', $logged ? $user->email : NULL) , array('disabled'=>'disabled')) ?>
        <?else:?>
        <?= Form::input('email', Arr::get($_POST,'email', $logged ? $user->email : NULL) , array('class'=>isset($errors['email']) ? 'error-input': '')) ?>
        <?endif?>

        <?= Form::label('phone', 'Телефон')?>
        <?= Form::input('phone', Arr::get($_POST,'phone', $logged ? $user->profile->phone : NULL) , array('class'=>isset($errors['phone']) ? 'error-input': '')) ?>

        <?= Form::label('site', 'Адрес сайта')?>
        <?= Form::input('site', Arr::get($_POST,'site')) ?>

        <?= Form::label('city_id', 'Регион ')?>
        <?= Form::hidden('city_id', Arr::get($_POST, 'city_id', $city_id) , array('id'=>'city_id')) ?>
        <?= Form::select('region', $regions, $region, array('class'=>isset($errors['city_id']) ? 'error-input': '', 'id'=>'region'))?>
        <span id="subRegion"><?= $cities ?></span>

        <?= Form::label('address', 'Адрес', array('class'=>'clear'))?>
        <?= Form::input('address', Arr::get($_POST,'address', $logged ? $user->profile->address : NULL) , array('class'=>isset($errors['address']) ? 'error-input': '')) ?>

        <?if(!Auth::instance()->logged_in()):?>
            <Br><?php echo Captcha::instance() ?>
            <?= Form::label('captcha', __('Enter captcha code')) ?>
            <?php echo Form::input('captcha', NULL, array('id' => 'captcha-key'))?>
        <?endif?>
        <br><br>
        <?=Form::submit('update', __('Add ad'), array('class' => 'pure-button pure-button-primary left'));  ?>
        <?= Form::label('termagree', Form::checkbox('termagree', 1, TRUE) . HTML::anchor('/page/terms', __('You\'ve agreed with terms and conditions of ads publication'), array('target'=>'_blank')), array('class'=>'left')) ?>
    </fieldset>
<?=Form::close()?>

<script type="text/javascript">
var job_ids = <?php echo json_encode($job_ids)?>;
var noprice_ids = <?php echo json_encode($noprice_ids)?>;
</script>