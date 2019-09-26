<?php

return [
    'channels' => [
        'event' => [
            'log' => 'event.log',
            'daily' => false,
            'level' => 'debug'
        ],
        'payment' => [
            'log' => 'payment.log',
            'daily' => true,
            'level' => 'info'
        ],
        'debug' => [
            'log' => 'debug.log',
            'daily' => true,
            'level' => 'debug'
        ],
        'error' => [
            'log' => 'error.log',
            'daily' => true,
            'level' => 'debug'
        ],
        'order' => [
            'log' => 'debug.log',
            'daily' => true,
            'level' => 'debug'
        ],
    ]
];
