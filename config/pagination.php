<?php

return array
(
    'board' => array(
        'total_items'       => 0,
        'items_per_page'    => 30,
        'current_page'      => array
        (
            'source'        => 'route',
            'key'           => 'page',
        ),
        'view'              => 'board/pagination',
        'auto_hide'         => TRUE,
        'first_page_in_url' => FALSE,
        'count_out' => 3,
        'count_in' => 3,
    ),
);