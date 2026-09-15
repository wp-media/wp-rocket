<?php

return [
    'testShouldHideWithOneDotComCDNEnabled' => [
        'config' => [
			'onecom_performance_plugin_enabled' => true,
            'show' => true,
            'mode' => 'byocdn',
            'oc_cdn_enabled' => true,
        ],
        'expected' => [
            'return' => false,
        ],
    ],
    'testShouldDisplayWithOneDotComCDNDisabled' => [
	    'config' => [
		    'onecom_performance_plugin_enabled' => true,
		    'show' => true,
		    'mode' => 'byocdn',
		    'oc_cdn_enabled' => false,
	    ],
	    'expected' => [
		    'return' => true,
	    ],
    ],
    'testShouldDisplayWithPluginDisabled' => [
	    'config' => [
		    'onecom_performance_plugin_enabled' => false,
		    'show' => true,
		    'mode' => 'byocdn',
		    'oc_cdn_enabled' => false,
	    ],
	    'expected' => [
		    'return' => true,
	    ],
    ],
];
