<?php

return [
    'testShouldReturnNewArrayWithOneDotComCDNEnabled' => [
        'config' => [
	        'onecom_performance_plugin_enabled' => true,
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
	        'onecom_performance_plugin_enabled' => true,
            'cname' => null,
            'oc_cdn_enabled' => false,
            'domain' => 'example.com',
        ],
        'expected' => [
            'return' => null,
        ],
    ],
];
