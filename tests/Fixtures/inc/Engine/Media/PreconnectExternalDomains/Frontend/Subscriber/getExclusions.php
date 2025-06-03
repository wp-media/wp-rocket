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
                'preconnect_external_domains_exclusions' => 'static.cloudflareinsights.com',
            ],
        ],
        'expected' => [
            'static.cloudflareinsights.com',
        ],
    ],
    'testShouldReturnDynamicListExclusions' => [
        'config' => [
            'get_lists' => (object) [
                'preconnect_external_domains_exclusions' => [
                    'static.cloudflareinsights.com',
                    'rel="profile"',
                    'rel="preconnect',
                ],
            ],
        ],
        'expected' => [
            'static.cloudflareinsights.com',
            'rel="profile"',
            'rel="preconnect',
        ],
    ],
];