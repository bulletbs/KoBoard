<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for import ads  from another board
 * Use for source tables
 * - ads_source
 * - users_source
 * - users_profile_source
 *
 * MySQL updates (data fix)

 */
class Task_BoardReImport extends Minion_Task
{
    CONST ALL_AMOUNT = 100000;
    CONST ONE_STEP_AMOUNT = 2000;
//    CONST ALL_AMOUNT = 50000;
//    CONST ONE_STEP_AMOUNT = 2000;
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
//        set_time_limit(600);
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
                $pos = (int) file_get_contents(DOCROOT . 'minion_reimport_last_id.log');
//                $pos = DB::select(DB::expr('max(id) max'))->from('ads')->execute();
//                $pos = $pos[0]['max'] ? $pos[0]['max'] : 0;
            }

            $sql = DB::select()
                ->from('ads_source')
                ->where('id','>', (int) $pos)
                ->and_where('user_id','>', 0)
                ->order_by('id','ASC')
                ->limit(self::ONE_STEP_AMOUNT )
                ->as_assoc();
            $result = $sql->execute();
            $amount += count($result);
            print 'Taking '. ($i+1).' portion of '. self::ONE_STEP_AMOUNT  .' rows to import (ID from '. $pos .')'.PHP_EOL;
            foreach($result as $row){
                try{
                    /* User setup */
                    if($row['user_id'] > 0){
                        if(isset($this->old2new[ $row['user_id'] ]))
                            $row['user_id'] = $this->old2new[ $row['user_id'] ];
                        else{
                            $user = DB::select()->from('users_source')->where('id', '=', $row['user_id'])->as_assoc()->execute();
                            if(isset($user[0])){
                                $user = $user[0];
                                $profile = DB::select()->from('user_profiles_source')->where('user_id', '=', $row['user_id'])->as_assoc()->execute();
                                if(isset($profile[0]))
                                    $user += $profile[0];
//                                echo Debug::vars($user);
//                                die();
                                $this->old2new[ $row['user_id'] ] = Model_User::reimport_user($user);
                                $row['user_id'] = $this->old2new[ $row['user_id'] ];
                            }
                        }
                    }

                    /* Impoting AD */
//                    $row['title'] = mb_strtolower($row['title']);
                    Model_BoardAd::reimport_ad($row);
                    $imported++;
                }
                catch(ORM_Validation_Exception $e){
                    file_put_contents(DOCROOT . 'minion_error.log', $row['id'] .' - '. implode(', ', $e->errors('validation/error')).PHP_EOL, FILE_APPEND);
                }
                $last_row_id = $row['id'];
            }
            unset($result);
        }
        file_put_contents(DOCROOT . 'minion_reimport_last_id.log', $last_row_id);
        print 'Operation taken '. (time() - $start) .' seconds for '. $imported . ' (of '. $amount .') records'.PHP_EOL;
    }
}