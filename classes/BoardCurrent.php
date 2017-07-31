<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class BoardCurrent
 *
 * Получение параметров из текущего запроса
 * Статические методы:
 * 1. city() - город (модификации: cityOf() для родительского падежа, cityIn() для местного падежа)
 * 2. category() - категория
 * 3. adsLink - ссылка на Объявления текущего раздела сайта
 */
class BoardCurrent {

    CONST CONFIG_FILE_NAME = 'board';

    /**
     * Получить текущий город из запроса
     * @param null $type
     * @return array|mixed
     */
    public static function city($type = NULL){
        $alias = Request::current()->param('city_alias');
        if(!empty($alias)){
            $city = Model_BoardCity::getCityIdByAlias($alias);
            if($city){
                switch($type){
                    case 'in':
                        return Model_BoardCity::getField('name_in', $city);
                        break;
                    case 'of':
                        return Model_BoardCity::getField('name_of', $city);
                        break;
                    default:
                        return Model_BoardCity::getField('name', $city);
                        break;
                }
            }
        }

        switch($type){
            case 'in':
//                return BoardConfig::instance()->in_country;
                return BoardConfig::instance()->all_country;
                break;
            case 'of':
                return BoardConfig::instance()->all_country;
                break;
            default:
                return BoardConfig::instance()->country_name;
                break;
        }
    }

    /**
     * Получить текузий город в родительском падеже
     * @return array|mixed
     */
    public static function cityOf(){
        return self::city('of');
    }

    /**
     * Получить текущий город в местном падеже
     * @return array|mixed
     */
    public static function cityIn(){
        return self::city('in');
    }

    /**
     * Получить текущую категорию из запроса
     * @return array|mixed
     */
    public static function category(){
        // TODO: функция вывода текущей категории,
        // что выводить если категория не выбрана?

    }

    /**
     * Сгененрировать ссылку на Объявления с текущими фильтрами
     * @param array $attributes
     * @return string
     */
    public static function adsLink($attributes = array()){
        $title = 'Объявления';

        $city_alias = Request::current()->param('city_alias');
        $cat_alias = Request::current()->param('cat_alias');
        if($city_alias && $city_alias != BoardConfig::instance()->country_alias){
            $href = Route::get('board_city')->uri(array(
                'city_alias' => $city_alias,
            ));
            $name = Model_BoardCity::getField('name_of', Model_BoardCity::getCityIdByAlias($city_alias));
            if(!empty($name) && is_string($name)){
                $title .= ' '.$name;
            }
            return HTML::anchor($href, $title, $attributes);
        }
        elseif($cat_alias){
            $href = Route::get('board_cat')->uri(array(
                'cat_alias' => $cat_alias,
            ));
            $name = Model_BoardCategory::getField('name', Model_BoardCategory::getCategoryIdByAlias($cat_alias));
            if(!empty($name) && is_string($name)){
                $title .= ' в категории '.$name;
            }
            return HTML::anchor($href, $title, $attributes);
        }
        else{
            $title .= ' '.BoardConfig::instance()->all_country;
        }
        return '<span'.HTML::attributes($attributes).'>'.$title.'</span>';
    }

}