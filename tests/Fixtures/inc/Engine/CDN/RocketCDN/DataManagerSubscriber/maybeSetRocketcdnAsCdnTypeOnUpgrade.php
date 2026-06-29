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
	],
];
