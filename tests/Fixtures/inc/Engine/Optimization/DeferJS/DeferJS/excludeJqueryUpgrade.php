<?php

return [
    'testShouldDoNothingWhenSafeModeDisabled' => [
        'config' => [
            'options' => [
                'defer_all_js_safe' => 0,
            ],
        ],
        'expected' => false,
    ],
    'testShouldUpdateOptionWhenSafeModeEnabled' => [
        'config' => [
            'options' => [
                'defer_all_js_safe' => 1,
            ],
        ],
        'expected' => [
            'defer_all_js_safe' => 1,
            'exclude_defer_js'  => [
                '/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
            ],
        ],
    ],
];