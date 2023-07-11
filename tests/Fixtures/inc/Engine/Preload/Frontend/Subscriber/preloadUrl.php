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
				'user-agent' => 'WP Rocket/Preload',
			],
			'config_mobile' => [
				'blocking' => false,
				'timeout'  => 0.01,
				'user-agent' => 'WP Rocket/Preload Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
			],
			'mobile_cache' => true,
			'user_agent' => 'WP Rocket/Preload Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1',
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
			'status' => 'pending',
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
				'user-agent' => 'WP Rocket/Preload',
			],
			'config_mobile' => [
				'blocking' => false,
				'timeout'  => 0.01,
				'user-agent' => 'WP Rocket/Preload Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
			],
			'mobile_cache' => true,
			'user_agent' => 'WP Rocket/Preload Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1',
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
			'status' => 'pending',
		]
	]
];
