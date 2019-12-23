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
        'umspay' => [
            'log' => 'umspay.log',
            'daily' => true,
            'level' => 'debug'
        ],
        'autotask' => [
            'log' => 'autotask.log',
            'daily' => true,
            'level' => 'debug'
        ],
        'process' => [//流程日志
            'log' => 'process.log',
            'daily' => true,
            'level' => 'debug'
        ],
    ]
];
