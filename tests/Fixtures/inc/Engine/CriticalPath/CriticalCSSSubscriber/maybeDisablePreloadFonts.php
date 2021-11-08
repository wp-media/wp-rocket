<?php

return [
    'vfs_dir'   => 'wp-content/cache/critical-css/',
	'structure' => [
		'wp-content' => [
			'cache'   => [
				'critical-css' => [
					'1' => [
						'posts'            => [
							'post-100.css' => '.post-10 { color: red; }',
						],
						'front_page.css'   => '.front_page { color: red; }',
					],
				],
			],
		],
	],
    'test_data' => [
        'testShouldReturnFalseWhenDONOTROCKETOPTIMIZE' => [
            'config'   => [
                'type'                           => 'front_page',
                'DONOTROCKETOPTIMIZE'            => true,
                'options'                        => [
                    'async_css'    => true,
                    'critical_css' => '',
                ],
                'is_rocket_post_excluded_option' => false,
                'get_current_page_critical_css'  => '',
            ],
            'expected' => false,
        ],
        'testShouldReturnFalseWhenAsyncDisabled' => [
            'config'   => [
                'type'                           => 'front_page',
                'DONOTROCKETOPTIMIZE'            => false,
                'options'                        => [
                    'async_css'    => false,
                    'critical_css' => '',
                ],
                'is_rocket_post_excluded_option' => false,
                'get_current_page_critical_css'  => '',
            ],
            'expected' => false,
        ],
        'testShouldReturnFalseWhenAsyncDisabledPost' => [
            'config'   => [
                'type'                           => 'is_post',
                'DONOTROCKETOPTIMIZE'            => false,
                'options'                        => [
                    'async_css'    => true,
                    'critical_css' => '',
                ],
                'is_rocket_post_excluded_option' => true,
                'get_current_page_critical_css'  => '',
            ],
            'expected' => false,
        ],
        'testShouldReturnFalseWhenNoCPCSS' => [
            'config'   => [
                'type'                           => 'is_page',
                'DONOTROCKETOPTIMIZE'            => false,
                'options'                        => [
                    'async_css'    => true,
                    'critical_css' => '',
                ],
                'is_rocket_post_excluded_option' => false,
                'get_current_page_critical_css'  => '',
            ],
            'expected' => false,
        ],
        'testShouldReturnTrueWhenCPCSS' => [
            'config'   => [
                'type'                           => 'front_page',
                'DONOTROCKETOPTIMIZE'            => false,
                'options'                        => [
                    'async_css'    => true,
                    'critical_css' => '',
                ],
                'is_rocket_post_excluded_option' => false,
                'get_current_page_critical_css'  => 'Critical CSS content',
            ],
            'expected' => true,
        ],
        'testShouldReturnTrueWhenCPCSSFallback' => [
            'config'   => [
                'type'                           => 'is_page',
                'DONOTROCKETOPTIMIZE'            => false,
                'options'                        => [
                    'async_css'    => true,
                    'critical_css' => 'Critical CSS Fallback',
                ],
                'is_rocket_post_excluded_option' => false,
                'get_current_page_critical_css'  => '',
            ],
            'expected' => true,
        ],
    ],
];