<?php defined('SYSPATH') or die('No direct script access.');?>
<h1><?php echo __('Ad edit')?></h1>
<?=Form::open('', array('class' => 'pure-form  pure-form-stacked', 'enctype' => 'multipart/form-data','id'=>'addForm'))?>
<?if(isset($errors)) echo View::factory('error/validation', array('errors'=>$errors))->render()?>
    <fieldset>
        <legend>Описание объявления</legend>
        <?= Form::label('title', 'Заголовок')?>
        <?= Form::input('title', Arr::get($_POST,'title', $model->title), array('class'=>isset($errors['title']) ? 'error-input': ''))?>

        <?= Form::label('category_id', 'Категория')?>
        <?= Form::hidden('category_id', Arr::get($_POST,'category_id', $model->category_id), array('id'=>'mainCategory')) ?>
        <?= Form::select('cat_main', $categories_main, Arr::get($_POST,'cat_main', $model->pcategory_id), array('class'=>isset($errors['category_id']) ? 'error-input': '', 'id'=>'catMain'))?>
        <span id="subCategory"><?= $cat_child ?></span>
        <div id="filter_holder"><?= $filters ?></div>

        <?= Form::label('description', 'Описание')?>
        <?= Form::textarea('description', Arr::get($_POST,'description', $model->description), array('class'=>isset($errors['description']) ? 'error-input': ''))?>

        <?= Form::label('type', 'Тип объявления')?>
        <?= Form::select('type', KoMS::translateArray(Model_BoardAd::$adType), $model->type, array('class'=>(isset($errors['type']) ? 'error-input': ''), 'id'=>'eventType')) ?>

        <div id="price_holder">
            <div class="hspacer_10"></div>
            <label for="option-two" class="pure-radio clear">
                <?= Form::radio('price_type', 1, $model->price_type == 1, array('id'=>'option-two')) ?> <?php echo __('Change')?>
            </label>
            <label for="option-three" class="pure-radio">
                <?= Form::radio('price_type', 2, $model->price_type == 2, array('id'=>'option-three')) ?> <?php echo __('For free')?>
            </label>
            <label for="option-one" class="pure-radio">
                <?= Form::radio('price_type', 0, $model->price_type==0, array('id'=>'option-one', 'class'=>'left')) ?>
                <span class="price_label"><?php echo __('Price')?></span>
                <?= Form::input('price', $model->price, array('placeholder'=>'Цена ('.$price_value.')', 'class'=>(isset($errors['price']) ? 'error-input ': '').' left', 'id'=>'eventPrice')) ?>
            </label>
            <label id="trade_styler"><?= Form::hidden('trade', 0) ?><?= Form::checkbox('trade', 1, $model->trade==1) ?> <?php echo __('Trade')?></label>
            <div class="hspacer_10"></div>
        </div>

        <legend>Фотографии</legend>
        <?if(count($photos)):?>
            <?foreach($photos as $photo):?>
                <div class="pure-u-4-24">
                    <?= HTML::anchor($photo->getPhotoUri(), $photo->getThumbTag('',array('class'=>'thumbnail')), array('target'=>'_blank')) ?><br>
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
        <?= Form::hidden('city_id', Arr::get($_POST,'city_id', $model->city_id) , array('id'=>'city_id')) ?>
        <?= Form::select('region', $regions, Arr::get($_POST,'region', $model->pcity_id), array('class'=>isset($errors['city_id']) ? 'error-input': '', 'id'=>'region'))?>
        <span id="subRegion"><?= $cities ?></span>

        <?= Form::label('address', 'Адрес', array('class'=>'clear'))?>
        <?= Form::input('address', Arr::get($_POST,'address', $model->address)) ?>
        <br><br>
        <?=Form::submit('update', __('Save ad'), array('class' => 'pure-button pure-button-primary'));  ?>
    </fieldset>
<?=Form::close()?>

<script type="text/javascript">
    <?if($model->loaded()):?>
    var job_ids = <?php echo json_encode($job_ids)?>;
    var noprice_ids = <?php echo json_encode($noprice_ids)?>;
    var modelId = <?php echo $model->id?>;
    <?endif?>
</script>