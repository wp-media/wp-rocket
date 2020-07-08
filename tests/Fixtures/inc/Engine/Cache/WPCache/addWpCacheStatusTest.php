<?php

return [
    'test_data' => [
        'testShouldAddTestToEmptyArray' => [
            'tests' => [],
            'expected' => [
                'direct' => [
                    'wp_cache_status' => [
                        'label' => 'WP_CACHE value',
                    ],
                ],
            ],
        ],
        'testShouldAddTestToExistingArray' => [
            'tests' => [
                'direct' => [
                    'api' => [],
                ],
                'async'  => [],
            ],
            'expected' => [
                'direct' => [
                    'wp_cache_status' => [
                        'label' => 'WP_CACHE value',
                    ],
                ],
            ],
        ],
    ],
];