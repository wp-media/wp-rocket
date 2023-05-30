<?php
return [
    'pluginDisabledShouldReturnSame' => [
        'config' => [
              'enable' => true,
			  'plugin_active' => false,
			  'cloudflare_api_email' => 'email@test.test',
			  'cloudflare_api_key' => '1ef242',
        ],
        'expected' => true
    ],
    'emptyEmailShouldReturnSame' => [
	    'config' => [
		    'enable' => true,
		    'plugin_active' => true,
		    'cloudflare_api_email' => '',
		    'cloudflare_api_key' => '1ef242',
	    ],
	    'expected' => true
    ],
    'emptyAPIKeyShouldReturnSame' => [
	    'config' => [
		    'enable' => true,
		    'plugin_active' => true,
		    'cloudflare_api_email' => 'email@test.test',
		    'cloudflare_api_key' => '',
	    ],
	    'expected' => true
    ],
	'shouldReturnFalse' => [
		'config' => [
			'enable' => true,
			'plugin_active' => true,
			'cloudflare_api_email' => 'email@test.test',
			'cloudflare_api_key' => '1ef242',
		],
		'expected' => false
	]
];
