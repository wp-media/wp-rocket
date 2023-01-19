<?php

return [
    'testShouldReturnNewArrayWithOneDotComCDNEnabled' => [
        'config' => [
            'zone' => null,
            'oc_cdn_enabled' => true,
        ],
        'expected' => [
            'return' => [
                'all',
            ],
        ],
    ],
    'testShouldReturnNullWithOneDotComCDNDisabled' => [
        'config' => [
            'zone' => null,
            'oc_cdn_enabled' => false,
        ],
        'expected' => [
            'return' => null,
        ],
    ],
];
