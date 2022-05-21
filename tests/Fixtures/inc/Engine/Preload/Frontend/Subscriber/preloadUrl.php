<?php
return [
	'preloadWithFailUrlShouldContinue' => [
		'config' => [
			'existing_job' => [
				'url' => 'url',
				'status' => 'pending',
			],
			'url' => 'url',
			'config' => [
				'blocking' => false,
				'timeout'  => 0.01,
			],
			'config_mobile' => [
				'blocking' => false,
				'timeout'  => 0.01,
				'user-agent' => 'user_agent'
			],
			'mobile_cache' => true,
			'user_agent' => 'user_agent',
			'process_generate' => [
				'is_wp_error' => true,
				'response' => 'content'
			],
			'process_mobile_generate' => [
				'is_wp_error' => true,
				'response' => 'content'
			]
		],
		'expected' => [
			'url' => 'url',
			'status' => 'completed',
		]
	],
	'preloadNoMobileShouldPreloadOnlyOnce' => [
		'config' => [
			'existing_job' => [
				'url' => 'url',
				'status' => 'pending',
			],
			'url' => 'url',
			'mobile_cache' => false,
		],
		'expected' => [
			'url' => 'url',
			'status' => 'completed',
		]
	],
	'preloadWithSuccessShouldContinue' => [
		'config' => [
			'existing_job' => [
				'url' => 'url',
				'status' => 'pending',
			],
			'url' => 'url',
			'config' => [
				'blocking' => false,
				'timeout'  => 0.01,
			],
			'config_mobile' => [
				'blocking' => false,
				'timeout'  => 0.01,
				'user-agent' => 'user_agent'
			],
			'mobile_cache' => true,
			'user_agent' => 'user_agent',
			'process_generate' => [
				'is_wp_error' => true,
				'response' => 'content'
			],
			'process_mobile_generate' => [
				'is_wp_error' => true,
				'response' => 'content'
			]
		],
		'expected' => [
			'url' => 'url',
			'status' => 'completed',
		]
	]
];
