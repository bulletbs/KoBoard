<?php
/**
 * Class BoardTags
 */
class BoardTerms {

    static public $rearray = array("~","!","@","#","$","%","^","&","*","(",")","_","+","`",'"',"№",";",":","?","-","=","|","\"","","/",
    "[","]","{","}","'",",",".","<",">","rn","n","t","«","»");

    static public $adjectivearray = array(
        "ые","ое","ие","ий","ая","ый","ой","ми","ых","ее","ую","их","ым",
        "как","для","что","или","это","этих",
        "всех","вас","они","оно","еще","когда",
        "где","эта","лишь","уже","вам","нет",
        "если","надо","все","так","его","чем",
        "без",
        "при","даже","мне","есть","только","очень",
        "сейчас","точно","обычно"
    );

    static public function seokeywords($contents,$symbol=5,$words=35){
//        $profiler = Profiler::start('tagen','Title tags generation');
        $contents = @preg_replace(array("'<[/!]*?[^<>]*?>'si","'([rn])[s]+'si","'&[a-z0-9]{1,6};'si","'( +)'si"),
            array("","1 "," "," "),strip_tags($contents));

        $contents = @str_replace(static::$rearray," ",$contents);
        $contents = mb_strtolower($contents);

        $keywordcache = @explode(" ",$contents);
        static::$rearray = array();

        foreach($keywordcache as $word){
            if(mb_strlen($word)>=$symbol && !is_numeric($word)){
                $adjective = mb_substr($word,-2);
                if(!in_array($adjective,static::$adjectivearray) && !in_array($word,static::$adjectivearray)){
                    static::$rearray[$word] = (array_key_exists($word,static::$rearray)) ? (static::$rearray[$word] + 1) : 1;
                }
            }
        }

        @arsort(static::$rearray);
        $keywordcache = @array_slice(static::$rearray,0,$words);
        $keywords = array();

        foreach($keywordcache as $word=>$count)
            $keywords[] = trim($word);

//        Profiler::stop($profiler);
        return $keywords;
    }

    /**
     * Generate term links list
     * @param $terms
     * @return mixed
     */
    public static function genList($terms){
        foreach ($terms as $_term_id=>$_term)
            $terms[$_term_id] = static::getUri($_term);
        return $terms;
    }

    /**
     * Return term uri
     * @param $term
     * @return mixed
     */
    public static function getUri($term){
        $uri = Route::get('board_term')->uri(array(
            'term' => $term
        ));
        return $uri;
    }
}
