<?php
return [
    'sameShouldReturnFalse' => [
        'config' => [
			'rocket_option' => [
				'minify_js' => true,
				'cdn' => false,
				'cache_mobile' => true,
			],
			'created_hash' => '88d74bf313c2e9e8aaeef4a05c29486c',
			'last_option_hash' => '88d74bf313c2e9e8aaeef4a05c29486c',
        ],
        'expected' => false
    ],

	'differentShouldReturnTrue' => [
		'config' => [
			'rocket_option' => [
				'minify_js' => false,
				'cdn' => false,
				'cache_mobile' => true,
			],
			'created_hash' => '88d74bf313c2e9e8aaeef4a05c29486c',
			'last_option_hash' => '88d74bf313c2e9e8aaeef4a05c29486d',
		],
		'expected' => true
	],
	'differentButIgnoredShouldReturnFalse' => [
		'config' => [
			'rocket_option' => [
				'minify_js' => true,
				'cdn' => false,
				'cache_mobile' => false,
			],
			'created_hash' => '88d74bf313c2e9e8aaeef4a05c29486c',
			'last_option_hash' => '88d74bf313c2e9e8aaeef4a05c29486c',
		],
		'expected' => false
	],
	'noOldConfigurationShouldReturnTrue' => [
		'config' => [
			'rocket_option' => [
				'minify_js' => true,
				'cdn' => false,
				'cache_mobile' => false,
			],
			'created_hash' => '88d74bf313c2e9e8aaeef4a05c29486c',
			'last_option_hash' => false,
		],
		'expected' => true
	],
	'noConfigurationShouldReturnTrue' => [
		'config' => [
			'rocket_option' => false,
			'created_hash' => '88d74bf313c2e9e8aaeef4a05c29486c',
			'last_option_hash' => '88d74bf313c2e9e8aaeef4a05c29486c',
		],
		'expected' => true
	],
];
