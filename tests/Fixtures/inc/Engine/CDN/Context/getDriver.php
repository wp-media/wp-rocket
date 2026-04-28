<?php

return [
	'testShouldReturnByocdnDriver' => [
		'config'   => [
			'cdn_type'      => 'byocdn',
			'subscription'  => [],
		],
		'expected' => 'byocdn',
	],
	'testShouldResolveRocketcdnToFreeWhenInactive' => [
		'config'   => [
			'cdn_type' => 'rocketcdn',
			'subscription' => [
				'is_active'           => false,
				'subscription_status' => 'cancelled',
			],
		],
		'expected' => 'rocketcdn_free',
	],
	'testShouldResolveRocketcdnToFreeWhenNotRunning' => [
		'config'   => [
			'cdn_type' => 'rocketcdn',
			'subscription' => [
				'is_active'           => true,
				'subscription_status' => 'paused',
			],
		],
		'expected' => 'rocketcdn_free',
	],
	'testShouldResolveRocketcdnToPaidWhenRunning' => [
		'config'   => [
			'cdn_type' => 'rocketcdn',
			'subscription' => [
				'is_active'           => true,
				'subscription_status' => 'running',
			],
		],
		'expected' => 'rocketcdn_paid',
	],
];
