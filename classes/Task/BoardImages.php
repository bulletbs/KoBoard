<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board ads importation
 */
class Task_BoardImages extends Minion_Task
{
    CONST ALL_AMOUNT = 200000;
    CONST ONE_STEP_AMOUNT = 10000;
//    CONST ALL_AMOUNT = 50;
//    CONST ONE_STEP_AMOUNT = 10;
//    CONST ALL_AMOUNT = 5;
//    CONST ONE_STEP_AMOUNT = 1;

    public $old2new = array();
    public $email2id = array();

    /**
     * Generate sitemaps
     */
    protected function _execute(Array $params){
        set_time_limit(0);
        Kohana::$environment = Kohana::PRODUCTION;
        $start = time();
        $amount = 0;
        $imported = 0;
        $last_row_id = 0;

        /**
         * Importing
         */
        for($i=0; $i*self::ONE_STEP_AMOUNT < self::ALL_AMOUNT; $i++){
            if($last_row_id)
                $pos = $last_row_id;
            else{
                $pos = DB::select(DB::expr('max(ad_id) max'))->from('ad_photos')->execute();
                $pos = $pos[0]['max'] ? $pos[0]['max'] : 0;
            }

            $sql = ORM::factory('BoardAd')
                ->where('id','>', (int) $pos)
                ->order_by('id','ASC')
                ->limit(self::ONE_STEP_AMOUNT );
            $result = $sql->find_all()->as_array('id');
            $amount += count($result);
//            print 'Taking '. ($i+1).' portion of '. self::ONE_STEP_AMOUNT  .' rows to import (ID from '. $pos .')'.PHP_EOL;
            file_put_contents(DOCROOT . 'minion_images.log', 'Taking '. ($i+1).' portion of '. self::ONE_STEP_AMOUNT  .' rows to import (ID from '. $pos .')'.PHP_EOL, FILE_APPEND);
            foreach($result as $row){
                try{
                    $photos = DB::select('photo_name')->from('jb_photo')->where('id_message','=',$row->id)->as_assoc()->execute();
                    if(count($photos)){
                        $imported++;
                        file_put_contents(DOCROOT . 'minion_images.log', 'Move photos of AD # '.$row->id.PHP_EOL, FILE_APPEND);
                        foreach($photos as $_photo)
                            if(is_file(DOCROOT.'upload/normal/'.$_photo['photo_name'])){
                                $row->addPhoto( DOCROOT.'upload/normal/'.$_photo['photo_name'] );
                            }
                        $row->setMainPhoto();
                    }
                }
                catch(ORM_Validation_Exception $e){
                    file_put_contents(DOCROOT . 'minion_error.log', $row->id .' - '. implode(', ', $e->errors('validation/error')).PHP_EOL, FILE_APPEND);
                }
                $last_row_id = $row->id;
            }
            unset($result);
        }

        print 'Operation taken '. (time() - $start) .' seconds for '. $imported . ' (of '. $amount .') records'.PHP_EOL;
    }
}