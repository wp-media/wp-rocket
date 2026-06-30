<?php

return [
	'test_data' => [
		'testShouldBailWhenOldVersionIsEqualTo3_22_0_2'     => [
			'config'   => [
				'new_version'      => '3.22.0.2',
				'old_version'      => '3.22.0.2',
				'settings'         => [ 'cdn_type' => 'byocdn' ],
				'rocketcdn_status' => [
					'subscription_status' => 'cancelled',
					'website_status'      => 'pending_deletion',
				],
			],
			'expected' => [
				'cdn_type' => 'byocdn',
			],
		],
		'testShouldBailWhenOldVersionIsGreaterThan3_22_0_2' => [
			'config'   => [
				'new_version'      => '3.22.1',
				'old_version'      => '3.22.1',
				'settings'         => [ 'cdn_type' => 'byocdn' ],
				'rocketcdn_status' => [
					'subscription_status' => 'cancelled',
					'website_status'      => 'pending_deletion',
				],
			],
			'expected' => [
				'cdn_type' => 'byocdn',
			],
		],
		'testShouldBailWhenNotInGracePeriod'                => [
			'config'   => [
				'new_version'      => '3.22.0.2',
				'old_version'      => '3.22.0.1',
				'settings'         => [ 'cdn_type' => 'byocdn' ],
				'rocketcdn_status' => [
					'subscription_status' => 'running',
					'website_status'      => 'active',
				],
			],
			'expected' => [
				'cdn_type' => 'byocdn',
			],
		],
		'testShouldSetRocketcdnWhenInGracePeriod'           => [
			'config'   => [
				'new_version'      => '3.22.0.2',
				'old_version'      => '3.22.0.1',
				'settings'         => [ 'cdn_type' => 'byocdn' ],
				'rocketcdn_status' => [
					'subscription_status' => 'cancelled',
					'website_status'      => 'pending_deletion',
				],
			],
			'expected' => [
				'cdn_type' => 'rocketcdn',
			],
		],

		// TC-4.1: Grace period + CDN enabled (cdn=1) + CNAME prefilled → cdn_type set to rocketcdn, other settings untouched.
		'testShouldSetRocketcdnInGracePeriodWithCdnEnabledAndCnamePrefilled'  => [
			'config'   => [
				'new_version'      => '3.22.0.2',
				'old_version'      => '3.21.3',
				'settings'         => [
					'cdn_type'   => 'byocdn',
					'cdn'        => 1,
					'cdn_cnames' => [ 'https://test.delivery.rocketcdn.me' ],
				],
				'rocketcdn_status' => [
					'subscription_status' => 'cancelled',
					'website_status'      => 'pending_deletion',
				],
			],
			'expected' => [
				'cdn_type'   => 'rocketcdn',
				'cdn'        => 1,
				'cdn_cnames' => [ 'https://test.delivery.rocketcdn.me' ],
			],
		],

		// TC-4.2: Grace period + CDN disabled (cdn=0) + CNAME prefilled → cdn_type set to rocketcdn, cdn and cnames untouched.
		'testShouldSetRocketcdnInGracePeriodWithCdnDisabledAndCnamePrefilled' => [
			'config'   => [
				'new_version'      => '3.22.0.2',
				'old_version'      => '3.21.3',
				'settings'         => [
					'cdn_type'   => 'byocdn',
					'cdn'        => 0,
					'cdn_cnames' => [ 'https://test.delivery.rocketcdn.me' ],
				],
				'rocketcdn_status' => [
					'subscription_status' => 'cancelled',
					'website_status'      => 'pending_deletion',
				],
			],
			'expected' => [
				'cdn_type'   => 'rocketcdn',
				'cdn'        => 0,
				'cdn_cnames' => [ 'https://test.delivery.rocketcdn.me' ],
			],
		],

		// TC-4.3: Grace period + CDN enabled (cdn=1) + CNAME empty → cdn_type set to rocketcdn, cdn and cnames untouched.
		'testShouldSetRocketcdnInGracePeriodWithCdnEnabledAndCnameEmpty'      => [
			'config'   => [
				'new_version'      => '3.22.0.2',
				'old_version'      => '3.21.3',
				'settings'         => [
					'cdn_type'   => 'byocdn',
					'cdn'        => 1,
					'cdn_cnames' => [],
				],
				'rocketcdn_status' => [
					'subscription_status' => 'cancelled',
					'website_status'      => 'pending_deletion',
				],
			],
			'expected' => [
				'cdn_type'   => 'rocketcdn',
				'cdn'        => 1,
				'cdn_cnames' => [],
			],
		],

		// TC-4.4: Grace period + CDN disabled (cdn=0) + CNAME empty → cdn_type set to rocketcdn, cdn and cnames untouched.
		'testShouldSetRocketcdnInGracePeriodWithCdnDisabledAndCnameEmpty'     => [
			'config'   => [
				'new_version'      => '3.22.0.2',
				'old_version'      => '3.21.3',
				'settings'         => [
					'cdn_type'   => 'byocdn',
					'cdn'        => 0,
					'cdn_cnames' => [],
				],
				'rocketcdn_status' => [
					'subscription_status' => 'cancelled',
					'website_status'      => 'pending_deletion',
				],
			],
			'expected' => [
				'cdn_type'   => 'rocketcdn',
				'cdn'        => 0,
				'cdn_cnames' => [],
			],
		],

		// TC-6.1: Active paid subscription + CDN enabled (cdn=1) + CNAME prefilled → upgrade bails (not in grace period), settings unchanged.
		'testShouldBailWhenActiveSubscriptionWithCdnEnabled'                  => [
			'config'   => [
				'new_version'      => '3.22.0.2',
				'old_version'      => '3.21.3',
				'settings'         => [
					'cdn_type'   => 'rocketcdn',
					'cdn'        => 1,
					'cdn_cnames' => [ 'https://test.delivery.rocketcdn.me' ],
				],
				'rocketcdn_status' => [
					'subscription_status' => 'running',
					'plan_type'           => 'paid',
				],
			],
			'expected' => [
				'cdn_type'   => 'rocketcdn',
				'cdn'        => 1,
				'cdn_cnames' => [ 'https://test.delivery.rocketcdn.me' ],
			],
		],

		// TC-6.2: Active paid subscription + CDN disabled (cdn=0, manually paused) + CNAME prefilled → upgrade bails, manual pause preserved.
		'testShouldBailWhenActiveSubscriptionWithCdnDisabled'                 => [
			'config'   => [
				'new_version'      => '3.22.0.2',
				'old_version'      => '3.21.3',
				'settings'         => [
					'cdn_type'   => 'rocketcdn',
					'cdn'        => 0,
					'cdn_cnames' => [ 'https://test.delivery.rocketcdn.me' ],
				],
				'rocketcdn_status' => [
					'subscription_status' => 'running',
					'plan_type'           => 'paid',
				],
			],
			'expected' => [
				'cdn_type'   => 'rocketcdn',
				'cdn'        => 0,
				'cdn_cnames' => [ 'https://test.delivery.rocketcdn.me' ],
			],
		],
	],
];
