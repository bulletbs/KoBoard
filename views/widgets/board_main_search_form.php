<?php defined('SYSPATH') or die('No direct script access.');?>
<div class="main_search">
    <form action="/all.html" method="get">
        <input type="hidden" name="op" value="search">
        <input type="text" name="query" placeholder="Поиск по <?php echo $ads_count ?> объявлениям" value="" class="query_field">
        <input type="submit" value="" class="search_button">
    </form>
</div>