<?php
return [
    'shouldClear' => [
        'config' => [
			'is_valid' => true,
			'referer' => 'https://example.org/url',
			'home_url' => 'https://example.org',
			'user_id' => 10,
			'parsed_url' => [
				'scheme' => 'https',
				'host' => 'example.org'
			],
			'rucss' => true,
			'boxes' => [

			],

        ],
		'expected' => [
			'url' => 'https://example.org/url',
			'user_id' => 10,
			'cleared' => true,
			'boxes' => [
				'maybe_clear_cache_change_notice'
			]
		]
    ],
	'shouldClearRUCSSDisabled' => [
		'config' => [
			'is_valid' => true,
			'referer' => 'https://example.org/url',
			'home_url' => 'https://example.org',
			'user_id' => 10,
			'parsed_url' => [
				'scheme' => 'https',
				'host' => 'example.org'
			],
			'rucss' => false,
			'boxes' => [

			],

		],
		'expected' => [
			'url' => 'https://example.org/url',
			'user_id' => 10,
			'cleared' => true,
			'boxes' => [
				'maybe_clear_cache_change_notice'
			]
		]
	],
	'validationFailShouldBailOut' => [
		'config' => [
			'is_valid' => false,
			'referer' => 'https://example.org/url',
			'home_url' => 'https://example.org',
			'user_id' => 10,
			'parsed_url' => [
				'scheme' => 'https',
				'host' => 'example.org'
			],
			'rucss' => true,
			'boxes' => [

			],

		],
		'expected' => [
			'url' => 'https://example.org/url',
			'user_id' => 10,
			'cleared' => false,
			'boxes' => [
			]
		]
	],

];
