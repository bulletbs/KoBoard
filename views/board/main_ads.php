<?php defined('SYSPATH') OR die('No direct script access.');?>
</div>
<div class="main_header">
    <div class="container">
        <h1 class="main">Бесплатные объявления</h1>
        <ul class="mainPageCategories">
            <li><a class="cat_icon1" href="/all/biznes-i-uslugi.html" title="Бизнес и услуги">Бизнес и услуги</a></li>
            <li><a class="cat_icon3" href="/all/dom-i-sad.html" title="Дом и сад">Дом и сад</a></li>
            <li><a class="cat_icon5" href="/all/ebay.html" title="Электроника">Куплю</a></li>
            <li><a class="cat_icon7" href="/all/moda-i-stil.html" title="Мода и стиль">Мода и стиль</a></li>
            <li><a class="cat_icon9" href="/all/rabota.html" title="Работа">Работа</a></li>
            <li><a class="cat_icon2" href="/all/detskij-mir.html" title="Детский мир">Детский мир</a></li>
            <li><a class="cat_icon4" href="/all/zhivotnye.html" title="Животные">Животные</a></li>
            <li><a class="cat_icon6" href="/all/nedvizhimost.html" title="Недвижимость">Недвижимость</a></li>
            <li><a class="cat_icon8" href="/all/transport.html" title="Транспорт">Транспорт</a></li>
            <li><a class="cat_icon10" href="/all/ehlektronika.html" title="Электроника">Электроника</a></li>
            <li><a class="cat_icon11" href="/all/khobbi-otdykh-i-sport.html" title="Хобби, отдых и спорт">Хобби, отдых и спорт</a></li>
        </ul>
        <div class="clear"></div>
    </div>
</div>
<div class="container">
    <div class="line_nobg"></div>
    <section class="main_lastest">
        <h2>Последние фото объявления</h2>
        <?foreach($last_ads as $_ad):?>
            <div>
            <span class="img_wrapper"><?= HTML::anchor( $_ad->getUri(), HTML::image(isset($last_ads_photos[$_ad->id]) ? $last_ads_photos[$_ad->id]->getThumbUri() : "/assets/board/css/images/noimage.png", array('alt'=>htmlspecialchars($_ad->title))) )?></span>
            <?php echo HTML::anchor($_ad->getUri(), $_ad->getShortTitle(20), array('class'=>'last_link', 'title'=> $_ad->title))?>
            <b><?= $_ad->getPrice( BoardConfig::instance()->priceTemplate($_ad->price_unit) ) ?></b>
            </div><?endforeach?>
    </section>
</div>
<div class="container">


