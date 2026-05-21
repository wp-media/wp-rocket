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
	'testShouldReturnRocketcdnFreeWhenNoActiveSubscription' => [
		'config'   => [
			'cdn_type'                => 'rocketcdn',
			'has_active_subscription' => false,
		],
		'expected' => 'rocketcdn_free',
	],
];
