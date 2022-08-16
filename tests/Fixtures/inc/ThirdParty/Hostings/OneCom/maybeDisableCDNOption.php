<?php

return [
    'testShouldBailOutWithOneDotComCDNEnabled' => [
        'config' => [
            'cdn' => true,
            'oc_cdn_enabled' => true,
            'options' => [
                'cdn' => 1,
                'cdn_cnames' => [
                    'usercontent.one/wp/www.example.com',
                ],
                'cdn_zones' => [
                    'all',
                ],
            ],
        ],
        'expected' => [
            'cdn_cname' => 'usercontent.one/wp/www.example.com',
            'return' => true,
        ],
    ],
    'testShouldBailOutWithCDNDisabled' => [
        'config' => [
            'cdn' => false,
            'oc_cdn_enabled' => false,
            'options' => [
                'cdn' => 0,
                'cdn_cnames' => [],
                'cdn_zones' => [],
            ],
        ],
        'expected' => [
            'cdn_cname' => 'usercontent.one/wp/www.example.com',
            'return' => false,
        ],
    ],
    'testShouldDisableCDNWithOptions' => [
        'config' => [
            'cdn' => true,
            'oc_cdn_enabled' => false,
            'options' => [
                'cdn' => 0,
                'cdn_cnames' => [],
                'cdn_zones' => [],
            ],
        ],
        'expected' => [
            'cdn_cname' => 'usercontent.one/wp/www.example.com',
            'return' => false,
        ],
    ],
];
