<?php

return [
    'testShouldReturnTrueWithOneDotComCDNEnabled' => [
        'config' => [
			'onecom_performance_plugin_enabled' => true,
            'cdn' => null,
            'oc_cdn_enabled' => true,
        ],
        'expected' => [
            'return' => true,
        ],
    ],
    'testShouldReturnNullWithOneDotComCDNDisabled' => [
        'config' => [
	        'onecom_performance_plugin_enabled' => true,
            'cdn' => null,
            'oc_cdn_enabled' => false,
        ],
        'expected' => [
            'return' => null,
        ],
    ],
];
