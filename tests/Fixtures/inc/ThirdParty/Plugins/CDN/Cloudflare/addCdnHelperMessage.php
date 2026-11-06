<?php
return [
    'pluginEnabledShouldAddCloudflare' => [
        'config' => [
              'addons' => [],
			  'plugin_enabled' => true,
			  'cf_email' => 'email',
			  'cf_key' => 'key',
			  'cf_domain' => 'domain',
        ],
        'expected' => [
			'Cloudflare'
        ]
    ],
	'pluginDisabledShouldReturnSame' => [
		'config' => [
			'addons' => [],
			'plugin_enabled' => false,
			'cf_email' => 'email',
			'cf_key' => 'key',
			'cf_domain' => 'domain',
		],
		'expected' => [

		]
	]
];
