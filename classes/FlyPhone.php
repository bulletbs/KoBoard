<?

/**
 * Класс рендерига текстовых строк в растровый слой
 * Class FlyPhone
 */
class FlyPhone{
    CONST FONT_NAME = 'modules/board/arial.ttf';
    CONST FONT_SIZE = 15;

    /**
     * Creates canvas width text information
     * @param $string
     * @param int $width
     * @param int $height
     * @return Imagick
     */
    public static function draw_canvas($string, $width = 560, $height = 20){
        /* Create new imagick object */
        $draw = new ImagickDraw();
        $draw->setFontSize(FlyPhone::FONT_SIZE);
        $draw->setFont(FlyPhone::FONT_NAME);
        $draw->annotation(2, 16, $string);
        /* Create canvas */
        $canvas = new Imagick();
        $canvas->newImage($width, $height, "white");
        $canvas->drawImage($draw);
        $canvas->setImageFormat('png');

        return $canvas;
    }

}