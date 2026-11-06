<?php

return [
	// resolve_live() reads cdn/cdn_type directly from the raw options store, bypassing
	// get_rocket_option() and the apply_pause_on_rocketcdn_only filter. This is the core
	// fix for Issue 2: a byocdn user with cdn=0 was showing "Other CDN active" because the
	// filter returned 1 for byocdn users in non-admin (REST) context.
	'testShouldReturnNothingWhenByocdnStoredButCdnDisabled' => [
		'config'   => [
			'stored' => [
				'cdn'       => 0,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'byocdn',
			],
		],
		'expected' => 'nothing',
	],
	'testShouldReturnByocdnWhenByocdnStoredAndCdnEnabled'   => [
		'config'   => [
			'stored' => [
				'cdn'       => 1,
				'cdn_type'  => 'byocdn',
				'cdn_state' => 'byocdn',
			],
		],
		'expected' => 'byocdn',
	],
	'testShouldFollowStoredValueForRocketcdnPaid'           => [
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
