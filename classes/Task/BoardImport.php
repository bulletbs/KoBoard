<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board ads importation
 */
class Task_BoardImport extends Minion_Task
{
    CONST ALL_AMOUNT = 50000;
    CONST ONE_STEP_AMOUNT = 2000;
//    CONST ALL_AMOUNT = 50;
//    CONST ONE_STEP_AMOUNT = 10;

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
         * Loading parents
         */
        $category_parents = array();
        $result = DB::select()->from('ad_categories_jb')->order_by('id', 'ASC')->as_assoc()->execute();
        foreach($result as $res)
            $category_parents[$res['id']] = $res['parent_id'];
        $city_parents = array();
        $result = DB::select()->from('ad_cities')->order_by('id', 'ASC')->as_assoc()->execute();
        foreach($result as $res)
            $city_parents[$res['id']] = $res['parent_id'];

        /**
         * Importing
         */
        for($i=0; $i*self::ONE_STEP_AMOUNT < self::ALL_AMOUNT; $i++){
            if($last_row_id)
                $pos = $last_row_id;
            else{
                $pos = DB::select(DB::expr('max(id) max'))->from('ads')->execute();
                $pos = $pos[0]['max'] ? $pos[0]['max'] : 0;
            }

            $sql = DB::select()
                ->from('jb_board')
                ->where('id','>', (int) $pos)
                ->order_by('id','ASC')
                ->limit(self::ONE_STEP_AMOUNT )
                ->as_assoc();
            $result = $sql->execute();
            $amount += count($result);
            print 'Taking '. ($i+1).' portion of '. self::ONE_STEP_AMOUNT  .' rows to import (ID from '. $pos .')'.PHP_EOL;
            foreach($result as $row){
                try{
                    /* City & Region  */
                    if(!isset( $city_parents[$row['city_id']] ))
                        continue;
                    $row['pcity_id'] = $city_parents[$row['city_id']];

                    /* Category & Part */
                    if(!isset( $category_parents[$row['id_category']] ))
                        continue;
                    $row['pcategory_id'] = $category_parents[$row['id_category']];

                    /* User setup */
                    if($row['user_id'] > 0){
                        if(isset($this->old2new[ $row['user_id'] ]))
                            $row['user_id'] = $this->old2new[ $row['user_id'] ];
                        else{
                            $user = DB::select()->from('jb_user')->where('id_user', '=', $row['user_id'])->as_assoc()->execute();
                            if(isset($user[0])){
                                $user = $user[0];
                                if(empty($user['name']))
                                    $user['name'] = $row['autor'];
                                $this->old2new[ $row['user_id'] ] = Model_User::import_user($user);
                                $row['user_id'] = $this->old2new[ $row['user_id'] ];
                            }
                        }
                    }
                    if(!$row['user_id'] && !empty($row['email'])){
                        if(isset($this->email2id[ $row['email'] ]))
                            $row['user_id'] = $this->email2id[ $row['email'] ];
                        else{
                            $user = array(
                                'email' => $row['email'],
                                'name' => $row['autor'],
                                'phone' => $row['contacts'],
                                'address' => $row['address'],
                                'city_id' => $row['city_id'],
                                'activ' => 0,
                            );
                            $row['user_id'] = Model_User::import_user($user);
//                            print 'register user: '. $row['user_id']. PHP_EOL;
                            $this->email2id[ $row['email'] ] = $row['user_id'];
                        }
                    }
//                    if(!$row['user_id'] && empty($row['email'])){
//                        file_put_contents(DOCROOT . 'minion_error.log', $row['id'] .' - Пользователь не создан'.PHP_EOL, FILE_APPEND);
//                        continue;
//                    }

                    /* Impoting AD */
                    Model_BoardAd::import_ad($row);
                    $imported++;
                }
                catch(ORM_Validation_Exception $e){
                    file_put_contents(DOCROOT . 'minion_error.log', $row['id'] .' - '. implode(', ', $e->errors('validation/error')).PHP_EOL, FILE_APPEND);
                }
                $last_row_id = $row['id'];
            }
            unset($result);
        }

        print 'Operation taken '. (time() - $start) .' seconds for '. $imported . ' (of '. $amount .') records'.PHP_EOL;
    }
}