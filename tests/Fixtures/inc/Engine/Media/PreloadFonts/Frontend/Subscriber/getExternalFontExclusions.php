<?php

return [
    'testShouldReturnEmptyArrayWhenDynamicListAttributeNotSet' => [
        'config' => [
            'get_lists' => (object) [
                'delay_js_exclusions' => [
                    'jquery',
                    'analytics',
                    'grecaptcha'
                ],
            ],
        ],
        'expected' => [],
    ],
    'testShouldReturnEmptyArrayWhenDynamicListAttributeIsNotAnArray' => [
        'config' => [
            'get_lists' => (object) [
                'external_font_exclusions' => 'cdnjs.cloudflare.com',
            ],
        ],
        'expected' => [
            'cdnjs.cloudflare.com',
        ],
    ],
    'testShouldReturnDynamicListExclusions' => [
        'config' => [
            'get_lists' => (object) [
                'external_font_exclusions' => [
                    'cdnjs.cloudflare.com',
                    'unpkg.com',
                    'maxcdn.bootstrapcdn.com',
                ],
            ],
        ],
        'expected' => [
            'cdnjs.cloudflare.com',
            'unpkg.com',
            'maxcdn.bootstrapcdn.com',
        ],
    ],
];
