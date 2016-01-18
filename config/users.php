<?php defined('SYSPATH') or die('No direct script access');
return array(
    /* User settings */
    'user_submodels' => array(
        'user_ads' => array(
            'model' => 'BoardAd',
            'foreign' => 'user_id',
        ),
    ),
);
