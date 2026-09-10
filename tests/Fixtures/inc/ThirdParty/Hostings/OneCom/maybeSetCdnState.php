<?php

return [
    'testShouldReturnByocdnWithOneDotComCDNEnabled' => [
        'config' => [
			'onecom_performance_plugin_enabled' => true,
            'cdn_state' => null,
            'oc_cdn_enabled' => true,
        ],
        'expected' => [
            'return' => 'byocdn',
        ],
    ],
    'testShouldReturnUnchangedWithOneDotComCDNDisabled' => [
	    'config' => [
		    'onecom_performance_plugin_enabled' => true,
		    'cdn_state' => 'nothing',
		    'oc_cdn_enabled' => false,
	    ],
	    'expected' => [
		    'return' => 'nothing',
	    ],
    ],
    'testShouldReturnUnchangedWithOneDotComCDNEnabledAndPluginDisabled' => [
	    'config' => [
		    'onecom_performance_plugin_enabled' => false,
		    'cdn_state' => 'nothing',
		    'oc_cdn_enabled' => false,
	    ],
	    'expected' => [
		    'return' => 'nothing',
	    ],
    ],
];
