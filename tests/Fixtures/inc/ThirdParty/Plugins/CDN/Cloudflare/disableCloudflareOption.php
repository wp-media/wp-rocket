<?php
return [
    'pluginDisabledShouldReturnSame' => [
        'config' => [
			'enabled' => false,
			'plugin_active' => false,
			'cloudflare_api_email' => 'email@test.test',
			'cloudflare_api_key' => '1ef242',
        ],
	    'expected' => [
			'enabled' => false,
	    ]
    ],
	'emptyEmailShouldReturnSame' => [
		'config' => [
			'enabled' => true,
			'plugin_active' => true,
			'cloudflare_api_email' => '',
			'cloudflare_api_key' => '1ef242',
		],
		'expected' => [
			'enabled' => false,
		]
	],
	'emptyAPIKeyShouldReturnSame' => [
		'config' => [
			'enabled' => true,
			'plugin_active' => true,
			'cloudflare_api_email' => 'email@test.test',
			'cloudflare_api_key' => '',
		],
		'expected' => [
			'enabled' => false,
		]
	],
	'shouldReturnFalse' => [
		'config' => [
			'enabled' => true,
			'plugin_active' => true,
			'cloudflare_api_email' => 'email@test.test',
			'cloudflare_api_key' => '1ef242',
		],
		'expected' => [
			'enabled' => false,
		]
	]
];
