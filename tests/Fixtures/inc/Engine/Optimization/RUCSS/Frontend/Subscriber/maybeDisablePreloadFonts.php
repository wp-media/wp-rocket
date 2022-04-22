<?php

return [
    'test_data' => [
        'testShouldReturnFalseWhenDONOTROCKETOPTIMIZE' => [
            'config'   => [
                'type'                           => 'front_page',
                'DONOTROCKETOPTIMIZE'            => true,
                'options'                        => [
                    'remove_unused_css' => true,
                ],
                'is_rocket_post_excluded_option' => [
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
                    'remove_unused_css' => false,
                ],
                'is_rocket_post_excluded_option' => [
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
                    'remove_unused_css' => true,
                ],
                'is_rocket_post_excluded_option' => [
                    'remove_unused_css' => true,
				],
            ],
            'expected' => false,
        ],
        'testShouldReturnTrueWhenRUCSSEnabled' => [
            'config'   => [
                'type'                           => 'front_page',
                'DONOTROCKETOPTIMIZE'            => false,
                'options'                        => [
                    'remove_unused_css' => true,
                ],
                'is_rocket_post_excluded_option' => [
                    'remove_unused_css' => false,
				],
            ],
            'expected' => true,
        ],
    ],
];
