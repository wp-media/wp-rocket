<?php

return [
    'testShouldReturnFalseWhenDONOTROCKETOPTIMIZE' => [
        'config'   => [
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
];