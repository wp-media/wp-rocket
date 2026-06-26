<?php

return [
	'testShouldReturnByocdnDriver' => [
		'config'   => [
			'cdn_type' => 'byocdn',
		],
		'expected' => 'byocdn',
	],
	'testShouldReturnByocdnWhenUnknownType' => [
		'config'   => [
			'cdn_type' => 'something_else',
		],
		'expected' => 'byocdn',
	],
	'testShouldReturnRocketcdnPaidDriver' => [
		'config'   => [
			'cdn_type'                => 'rocketcdn',
			'has_active_subscription' => true,
			'is_paid'                 => true,
		],
		'expected' => 'rocketcdn_paid',
	],
	'testShouldReturnRocketcdnFreeWhenActiveButNotPaid' => [
		'config'   => [
			'cdn_type'                => 'rocketcdn',
			'has_active_subscription' => true,
			'is_paid'                 => false,
		],
		'expected' => 'rocketcdn_free',
	],
	'testShouldReturnRocketcdnWhenNoActiveSubscription' => [
		'config'   => [
			'cdn_type'                          => 'rocketcdn',
			'has_active_subscription'           => false,
			'is_cancelled_outside_grace_period' => true,
		],
		'expected' => 'rocketcdn',
	],
];
