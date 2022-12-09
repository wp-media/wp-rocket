<?php

return [
    'testShouldReturnNewArrayWithOneDotComCDNEnabled' => [
        'config' => [
            'cname' => null,
            'oc_cdn_enabled' => true,
            'domain' => 'example.com',
        ],
        'expected' => [
            'return' => [
                'usercontent.one/wp/www.example.com',
            ],
        ],
    ],
    'testShouldReturnNullWithOneDotComCDNDisabled' => [
        'config' => [
            'cname' => null,
            'oc_cdn_enabled' => false,
            'domain' => 'example.com',
        ],
        'expected' => [
            'return' => null,
        ],
    ],
];
