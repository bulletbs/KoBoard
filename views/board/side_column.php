<?php defined('SYSPATH') OR die('No direct script access.');

echo Widget::factory('BoardCityMenu')->cached(Date::DAY * 365)->render();
//echo Widget::factory('BoardCityMenu')->render();