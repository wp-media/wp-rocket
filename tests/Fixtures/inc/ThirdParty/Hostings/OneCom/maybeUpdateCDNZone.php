<?php

return [
    'testShouldReturnNewArrayWithOneDotComCDNEnabled' => [
        'config' => [
	        'onecom_performance_plugin_enabled' => true,
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
	        'onecom_performance_plugin_enabled' => true,
            'zone' => null,
            'oc_cdn_enabled' => false,
        ],
        'expected' => [
            'return' => null,
        ],
    ],
];
