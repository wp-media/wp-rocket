<?php

return [
    'test_data' => [
        'testShouldBailOutWhenSetCacheConstFilterFalse' => [
            'config' => [
				'filter_constant_value'   => true,
			],
			'tests'   => [
                'direct' => [
                    'api' => [],
                ],
            ],
			'expected' => [],
		],

        'testShouldAddTestToEmptyArray' => [
            'config' => [],
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
            'config' => [],
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