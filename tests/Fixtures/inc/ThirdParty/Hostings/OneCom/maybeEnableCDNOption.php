<?php

return [
    'testShouldReturnTrueWithOneDotComCDNEnabled' => [
        'config' => [
            'cdn' => null,
            'oc_cdn_enabled' => true,
        ],
        'expected' => [
            'return' => true,
        ],
    ],
    'testShouldReturnNullWithOneDotComCDNDisabled' => [
        'config' => [
            'cdn' => null,
            'oc_cdn_enabled' => false,
        ],
        'expected' => [
            'return' => null,
        ],
    ],
];
