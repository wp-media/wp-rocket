<?php
return [
    'testShouldReturnEmptyWhenOptionIsEmpty' => [
        'config' => [
            'options' => []
        ],
        'expected' => [],
    ],
    'testShouldReturnEmptyWhenOptionNotEnabled' => [
        'config' => [
            'options' => [
                'remove_unused_css' => [
                    'service' => 'rucss_debug_subscriber',
                    'class'   => 'WP_Rocket\Engine\Debug\RUCSS\Subscriber',
                    'enabled' => false,
                ],
            ],
        ],
        'expected' => [],
    ],
    'testShouldReturnExpectedWhenOptionIsEnabled' => [
        'config' => [
            'options' => [
                'remove_unused_css' => [
                    'service' => 'rucss_debug_subscriber',
                    'class'   => 'WP_Rocket\Engine\Debug\RUCSS\Subscriber',
                    'enabled' => true,
                ],
            ],
        ],
        'expected' => [
            [
                'service' => 'rucss_debug_subscriber',
                'class'   => 'WP_Rocket\Engine\Debug\RUCSS\Subscriber',
            ],
        ],
    ]
];