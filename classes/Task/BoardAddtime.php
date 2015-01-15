<?php defined('SYSPATH') or die('No direct script access.');

/**
 * MInion task for board ads addtime fix
 */
class Task_BoardAddtime extends Minion_Task
{
    CONST ALL_AMOUNT = 500000;
    CONST ONE_STEP_AMOUNT = 10000;

    /**
     * Generate sitemaps
     */
    protected function _execute(Array $params){
        set_time_limit(600);
        Kohana::$environment = Kohana::PRODUCTION;

        $start = time();
        $amount = 0;

        /**
         * Fixing
         */
        for($i=0; $i*self::ONE_STEP_AMOUNT < self::ALL_AMOUNT; $i++){
            $pos = DB::select(DB::expr('min(id) min'))->from('ads')->where('addtime','=',0)->execute();
            $result = DB::select()
                ->from('jb_board')
                ->select('id', 'date_add')
                ->where('id','>=', (int) $pos[0]['min'])
                ->order_by('id','ASC')
                ->limit(self::ONE_STEP_AMOUNT )
                ->as_assoc()->execute();
            $amount += count($result);
            print 'Taking '. ($i+1).' portion of '. self::ONE_STEP_AMOUNT  .' rows to fix (ID from '. $pos[0]['min'] .')'.PHP_EOL;
            foreach($result as $row){
                try{
                    $values = array(
                        'addtime' => strtotime($row['date_add']),
                    );
                    DB::update('ads')->set($values)->where('id','=', $row['id'])->execute();
                }
                catch(ORM_Validation_Exception $e){
                    file_put_contents(DOCROOT . 'minion_error.log', $row['id'] .' - '. implode(', ', $e->errors('validation/error')).PHP_EOL, FILE_APPEND);
                }
            }
            unset($result);
        }
        print 'Operation taken '. (time() - $start) .' seconds for '.  $amount  . ' records'.PHP_EOL;
    }
}