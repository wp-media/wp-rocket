<?php

return [
    'testShouldReturnTrueWithOneDotComCDNEnabled' => [
        'config' => [
			'onecom_performance_plugin_enabled' => true,
            'oc_cdn_enabled' => true,
        ],
        'expected' => [
            'return' => true,
        ],
    ],
    'testShouldReturnFalseWithOneDotComCDNDisabled' => [
	    'config' => [
		    'onecom_performance_plugin_enabled' => true,
		    'oc_cdn_enabled' => false,
	    ],
	    'expected' => [
		    'return' => false,
	    ],
    ],
    'testShouldReturnFalseWithOneDotComCDNEnabledAndPluginDisabled' => [
	    'config' => [
		    'onecom_performance_plugin_enabled' => false,
		    'oc_cdn_enabled' => false,
	    ],
	    'expected' => [
		    'return' => false,
	    ],
    ],
];
