<?php

return [
	'testShouldReflectCdnForcedOffAtReadTime'          => [
		'config'   => [
			'stored' => [
				'cdn'       => 1,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'byocdn',
			],
			'force'  => [
				'option' => 'cdn',
				'value'  => false,
			],
		],
		'expected' => 'nothing',
	],
	'testShouldReflectCdnTypeForcedToByocdnAtReadTime' => [
		'config'   => [
			'stored'       => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
			],
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'           => 'free',
			],
			'force'        => [
				'option' => 'cdn_type',
				'value'  => 'byocdn',
			],
		],
		'expected' => 'byocdn',
	],
	'testShouldFollowStoredValueWhenNothingIsForced'   => [
		'config'   => [
			'stored'       => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_paid',
			],
			'subscription' => [
				'subscription_status' => 'running',
				'plan_type'           => 'paid',
			],
			'token'        => 'fake-cdn-token',
		],
		'expected' => 'rocketcdn_paid',
	],
	'testShouldReturnRocketcdnFreeWhenNoTokenEvenIfSubscriptionShowsCancelled' => [
		'config'   => [
			'stored'       => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
			],
			'subscription' => [
				'subscription_status' => 'cancelled',
				'website_status'      => 'active',
			],
			// No 'token' key: rocketcdn_user_token is absent, simulating a fresh install.
			// The bridge must skip the cancellation check when there is no token,
			// since the APIClient returns 'cancelled' as its hardcoded default in that state.
		],
		'expected' => 'rocketcdn_free',
	],
];
