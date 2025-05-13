<?php

return [
    'testShouldReturnEmptyArrayWhenDyanmicListAttributeNotSet' => [
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
    'testShouldReturnEmptyArrayWhenDyanmicListAttributeIsNotAnArray' => [
        'config' => [
            'get_lists' => (object) [
                'preload_fonts_exclusions' => '',
            ],
        ],
        'expected' => [],
    ],
    'testShouldReturnDynamicListExclusions' => [
        'config' => [
            'get_lists' => (object) [
                'preload_fonts_exclusions' => [
                    'Montserrat',
                    'https://fonts.gstatic.com/s/lato/v24/S6uyw4BMUTPHjx4wWw.woff2',
                ],
            ],
        ],
        'expected' => [
            'Montserrat',
            'https://fonts.gstatic.com/s/lato/v24/S6uyw4BMUTPHjx4wWw.woff2',
        ],
    ],
];