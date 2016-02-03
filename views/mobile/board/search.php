<?php defined('SYSPATH') OR die('No direct script access.');?>
<h1 class="uk-h2"><?echo !$search_by_user ? "Все объявления в ". $title : $title?></h1>

<?if(!$search_by_user):?>
<?if(isset($city_counter)):?>
    <div class="uk-hidden" id="allCityList">
        <h3 class="tm-search-subtitle">Населенные пункты <a href="#" class="uk-close uk-toggle" data-uk-toggle="{target:'#allCityList'}"></a></h3>
        <ul class="uk-list uk-column-large-1-5 uk-column-medium-1-4 uk-column-small-1-2">
        <?foreach($city_counter as $_city_id=>$_city):?>
            <li><?php echo HTML::anchor(Model_BoardCity::generateUri( Model_BoardCity::getField('alias', $_city['city_id'])), Model_BoardCity::getField('name', $_city['city_id']) . ' <span class="uk-text-small uk-text-muted">' .$_city['cnt']. '</span>') ?></li><?endforeach?>
        </ul>
    </div>
<?elseif(is_null($city)):?>
<div class="uk-hidden" id="allCityList">
    <h3 class="tm-search-subtitle">Регионы <a href="#" class="uk-close uk-toggle" data-uk-toggle="{target:'#allCityList'}"></a></h3>
    <ul class="uk-list uk-column-large-1-5 uk-column-medium-1-4 uk-column-small-1-2">
    <?foreach($regions as $_region_id=>$_region):?>
        <li><?php echo HTML::anchor(Model_BoardCity::generateUri( Model_BoardCity::getField('alias', $_region_id)), $_region) ?></li><?endforeach?>
    </ul>
</div>
<?endif?>
<?if(count($childs_categories)):?>
<div class="uk-hidden" id="allCategoryList">
    <h3 class="tm-search-subtitle">Категории <a href="#" class="uk-close uk-toggle" data-uk-toggle="{target:'#allCategoryList'}"></a></h3>
    <ul class="uk-list uk-column-large-1-5 uk-column-medium-1-4 uk-column-small-1-2">
    <?foreach($childs_categories as $_cat_id=>$_cat):?>
        <li><?php echo HTML::anchor(Model_BoardCategory::generateUri($_cat->alias), $_cat->name) ?></li><?endforeach;?>
    </ul>
</div>
<?endif?>
<?if(isset($main_filter)):?>
<script type="text/javascript">
    var basecat_uri = '<?php echo $main_filter['base_uri'] ;?>';
    var subcat_options = <?php echo json_encode(array_flip($main_filter['aliases'])) ?>;
</script>
<div class="uk-hidden" id="allFilterList">
    <h3 class="tm-search-subtitle"><?php echo $main_filter['name']?> <a href="#" class="uk-close uk-toggle" data-uk-toggle="{target:'#allFilterList'}"></a></h3>
    <ul class="uk-list uk-column-large-1-6 uk-column-medium-1-4 uk-column-small-1-2">
    <?foreach($main_filter['options'] as $_opt_id=>$_opt):?>
        <li><?php echo HTML::anchor(Model_BoardFilter::generateUri($_opt['alias']), $_opt['value']) ?></li><?endforeach?>
    </ul>
</div>
<?endif?>
<div class="uk-button-group uk-float-right">
    <?if(is_null($city)):?><button class="uk-button" data-uk-toggle="{target:'#allCityList'}">Регионы</button><?endif?>
    <?if(isset($city_counter)):?><button class="uk-button" data-uk-toggle="{target:'#allCityList'}">Города</button><?endif?>
    <?if(count($childs_categories)):?><button class="uk-button" data-uk-toggle="{target:'#allCategoryList'}">Категории</button><?endif?>
    <?if(isset($main_filter)):?><button class="uk-button" data-uk-toggle="{target:'#allFilterList'}"><?php echo $main_filter['name']?></button><?endif?>
</div>
<ul class="uk-tab uk-float-left">
    <li<?php echo is_null(Arr::get($_GET, 'type')) ? ' class="uk-active"':''?>><?php echo HTML::anchor(Request::$current->uri() . URL::query(array('type'=>NULL)), __('Any'))?></li>
    <li<?php echo Arr::get($_GET, 'type')=== (string) Model_BoardAd::PRIVATE_TYPE ? ' class="uk-active"':''?>><?php echo HTML::anchor(Request::$current->uri() . URL::query(array('type'=>Model_BoardAd::PRIVATE_TYPE)), __(!is_null($category) && $category instanceof ORM && $category->job ? 'Resume' : 'Private'))?>
    <li<?php echo Arr::get($_GET, 'type')=== (string) Model_BoardAd::BUSINESS_TYPE ? ' class="uk-active"':''?>><?php echo HTML::anchor(Request::$current->uri() . URL::query(array('type'=>Model_BoardAd::BUSINESS_TYPE)), __(!is_null($category) && $category instanceof ORM && $category->job ? 'Vacancy' : 'Business'))?>
</ul>
<div class="uk-clearfix"></div>
<?endif?>

<?if(count($ads)):?>
<div class="uk-grid uk-margin-top uk-grid-width-small-1-2 uk-grid-width-medium-1-4 uk-grid-width-large-1-5">
<?foreach($ads as $ad):?>
<div>
    <div class="tm-results-block uk-margin-bottom uk-panel uk-panel-box uk-text-center">
        <a href="<?php echo URL::site().$ad->getUri()?>"><img src="<?php echo isset($photos[$ad->id]) ? $photos[$ad->id]->getThumbUri() : "/assets/board/css/images/noimage.png"?>" class="uk-thumbnail"></a>
        <div class="uk-text-small uk-margin-top">
            <?php echo HTML::anchor($ad->getUri(), $ad->getTitle(), array('title'=> $ad->getTitle(), 'class'=>''))?>
            <div class="uk-text-bold"><?= $ad->getPrice( BoardConfig::instance()->priceTemplate($ad->price_unit) ) ?></div>
        </div>
    </div>
</div>
<?endforeach;?>
</div>
<?php echo $pagination->render('mobile/board/pagination')?>

<?else:?>
 <div class="uk-margin uk-text-bold uk-text-large">К сожалению не удалось найти объявления по указаным критериям</div>
<?endif?>