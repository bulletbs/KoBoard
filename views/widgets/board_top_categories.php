<ul class="topcat_links">
    <li><?php echo HTML::anchor($route->uri(array('city_alias'=>$region, 'cat_alias'=>'transport')), 'Транспорт')?></li>
    <li><?php echo HTML::anchor($route->uri(array('city_alias'=>$region, 'cat_alias'=>'nedvizhimost')), 'Недвижимость')?></li>
    <li><?php echo HTML::anchor($route->uri(array('city_alias'=>$region, 'cat_alias'=>'rabota')), 'Работа')?></li>
    <li><?php echo HTML::anchor($route->uri(array('city_alias'=>$region, 'cat_alias'=>'biznes-i-uslugi')), 'Бизнес и услуги')?></li>
    <li>
        <a href="#">еще...</a>
        <ul>
            <li><?php echo HTML::anchor($route->uri(array('city_alias'=>$region, 'cat_alias'=>'detskij-mir')), 'Детский мир')?></li>
            <li><?php echo HTML::anchor($route->uri(array('city_alias'=>$region, 'cat_alias'=>'dom-i-sad')), 'Дом и сад')?></li>
            <li><?php echo HTML::anchor($route->uri(array('city_alias'=>$region, 'cat_alias'=>'zhivotnye')), 'Животные')?></li>
            <li><?php echo HTML::anchor($route->uri(array('city_alias'=>$region, 'cat_alias'=>'ebay')), 'Куплю')?></li>
            <li><?php echo HTML::anchor($route->uri(array('city_alias'=>$region, 'cat_alias'=>'moda-i-stil')), 'Мода и стиль')?></li>
            <li><?php echo HTML::anchor($route->uri(array('city_alias'=>$region, 'cat_alias'=>'khobbi-otdykh-i-sport')), 'Хобби, отдых и спорт')?></li>
            <li><?php echo HTML::anchor($route->uri(array('city_alias'=>$region, 'cat_alias'=>'ehlektronika')), 'Электроника')?></li>
        </ul>
    </li>
</ul>