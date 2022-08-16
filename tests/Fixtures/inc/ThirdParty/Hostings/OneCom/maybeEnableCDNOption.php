<?php

return [
    'testShouldBailOutWithOneDotComCDNDisabled' => [
        'config' => [
            'cdn' => false,
            'oc_cdn_enabled' => false,
            'domain' => 'example.com',
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
    'testShouldBailOutWithCDNEnabled' => [
        'config' => [
            'cdn' => true,
            'oc_cdn_enabled' => true,
            'domain' => 'example.com',
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
    'testShouldEnableCDNWithOptions' => [
        'config' => [
            'cdn' => false,
            'oc_cdn_enabled' => true,
            'domain' => 'example.com',
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
];
