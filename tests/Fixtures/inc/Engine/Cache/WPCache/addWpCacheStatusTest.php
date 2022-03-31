<?php

return [
    'test_data' => [
        'testShouldBailOutWhenSetCacheConstFilterFalse' => [
			'tests'   => [],
            'config' => [
				'set_filter_to_false'   => true,
			],
			'expected' => [
                'direct' => [
                    'wp_cache_status' => [
                        'label' => 'WP_CACHE value',
                    ],
                ],
            ],
		],

        'testShouldAddTestToEmptyArray' => [
            'config' => [
				'set_filter_to_false'   => false,
			],
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
            'config' => [
				'set_filter_to_false'   => false,
			],
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