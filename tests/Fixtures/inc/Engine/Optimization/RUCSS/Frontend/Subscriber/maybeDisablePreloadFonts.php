<?php

return [
    'test_data' => [
        'testShouldReturnFalseWhenDONOTROCKETOPTIMIZE' => [
            'config'   => [
                'type'                           => 'front_page',
                'DONOTROCKETOPTIMIZE'            => true,
                'options'                        => [
                    'async_css'         => true,
                    'remove_unused_css' => true,
                ],
                'is_rocket_post_excluded_option' => [
					'async_css'         => false,
                    'remove_unused_css' => false,
				],
            ],
            'expected' => false,
        ],
		'testShouldReturnFalseWhenRUCSSDisabled' => [
            'config'   => [
                'type'                           => 'front_page',
                'DONOTROCKETOPTIMIZE'            => false,
                'options'                        => [
                    'async_css'         => true,
                    'remove_unused_css' => false,
                ],
                'is_rocket_post_excluded_option' => [
					'async_css'         => false,
                    'remove_unused_css' => false,
				],
            ],
            'expected' => false,
        ],
		'testShouldReturnFalseWhenRUCSSDisabledPost' => [
            'config'   => [
                'type'                           => 'is_post',
                'DONOTROCKETOPTIMIZE'            => false,
                'options'                        => [
                    'async_css'         => true,
                    'remove_unused_css' => true,
                ],
                'is_rocket_post_excluded_option' => [
					'async_css'         => false,
                    'remove_unused_css' => true,
				],
            ],
            'expected' => false,
        ],
        'testShouldReturnTrueWhenAsyncDisabled' => [
            'config'   => [
                'type'                           => 'front_page',
                'DONOTROCKETOPTIMIZE'            => false,
                'options'                        => [
                    'async_css'         => false,
                    'remove_unused_css' => true,
                ],
                'is_rocket_post_excluded_option' => [
					'async_css'         => false,
                    'remove_unused_css' => false,
				],
            ],
            'expected' => true,
        ],
        'testShouldReturnTrueWhenAsyncDisabledPost' => [
            'config'   => [
                'type'                           => 'is_post',
                'DONOTROCKETOPTIMIZE'            => false,
                'options'                        => [
                    'async_css'         => true,
                    'remove_unused_css' => true,
                ],
                'is_rocket_post_excluded_option' => [
					'async_css'         => true,
                    'remove_unused_css' => false,
				],
            ],
            'expected' => true,
        ],
    ],
];
