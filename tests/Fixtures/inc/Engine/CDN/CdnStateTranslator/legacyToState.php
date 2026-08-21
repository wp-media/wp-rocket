<?php

return [
	'testShouldReturnNothingWhenCdnDisabled'                    => [
		'settings' => [
			'cdn'      => 0,
			'cdn_type' => 'rocketcdn',
		],
		'config'   => [],
		'expected' => 'nothing',
	],
	'testShouldReturnNothingWhenCdnKeyMissing'                  => [
		'settings' => [],
		'config'   => [],
		'expected' => 'nothing',
	],
	'testShouldReturnByocdnWhenDriverIsByocdn'                  => [
		'settings' => [
			'cdn'      => 1,
			'cdn_type' => 'byocdn',
		],
		'config'   => [],
		'expected' => 'byocdn',
	],
	'testShouldDefaultToRocketcdnWhenCdnTypeMissing'            => [
		'settings' => [
			'cdn' => 1,
		],
		'config'   => [
			'has_active_subscription' => true,
			'is_paid'                 => false,
		],
		'expected' => 'rocketcdn_free',
	],
	'testShouldReturnRocketcdnPaidWhenActiveAndPaid'            => [
		'settings' => [
			'cdn'      => 1,
			'cdn_type' => 'rocketcdn',
		],
		'config'   => [
			'has_active_subscription' => true,
			'is_paid'                 => true,
		],
		'expected' => 'rocketcdn_paid',
	],
	'testShouldReturnRocketcdnFreeWhenActiveButNotPaid'         => [
		'settings' => [
			'cdn'      => 1,
			'cdn_type' => 'rocketcdn',
		],
		'config'   => [
			'has_active_subscription' => true,
			'is_paid'                 => false,
		],
		'expected' => 'rocketcdn_free',
	],
	'testShouldReturnNothingWhenSubscriptionFullyCancelled'     => [
		'settings' => [
			'cdn'      => 1,
			'cdn_type' => 'rocketcdn',
		],
		'config'   => [
			'has_active_subscription'           => false,
			'is_cancelled_outside_grace_period' => true,
		],
		'expected' => 'nothing',
	],
	'testShouldReturnRocketcdnFreeWhenInactiveButInGracePeriod' => [
		'settings' => [
			'cdn'      => 1,
			'cdn_type' => 'rocketcdn',
		],
		'config'   => [
			'has_active_subscription'           => false,
			'is_cancelled_outside_grace_period' => false,
			'is_paid'                            => false,
		],
		'expected' => 'rocketcdn_free',
	],
];
