<?php

return [
	'testShouldBailOutWithDisabledOneDotComCDN' => [
		'config'      => [
			'onecom_performance_plugin_enabled' => true,
			'oc_cdn_enabled' => false,
			'excluded' => [],
		],
		'expected' => [],
	],
	'testShouldBailOutWithEnabledOneDotComCDNAndPluginDisabled' => [
		'config'      => [
			'onecom_performance_plugin_enabled' => false,
			'oc_cdn_enabled' => true,
			'excluded' => [],
		],
		'expected' => [],
	],
	'testShouldExcludeFromCDN' => [
		'config'      => [
			'onecom_performance_plugin_enabled' => true,
            'oc_cdn_enabled' => true,
            'excluded' => [],
        ],
		'expected' => [
            '/wp-includes/(.*)'
        ],
	],
];
