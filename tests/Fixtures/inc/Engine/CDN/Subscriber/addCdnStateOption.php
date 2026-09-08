<?php
return [
	// Sites already on >= 3.23.4 have cdn_state — bail out without touching the DB.
	'shouldBailOutWhenOldVersionIsGreaterOrEqualTo3234'   => [
		'config'   => [
			'new_version' => '3.23.5',
			'old_version' => '3.23.4',
		],
		'expected' => null,
	],

	'shouldSetCdnStateWhenUpgradingFromBelow3234'         => [
		'config'   => [
			'new_version'           => '3.23.4',
			'old_version'           => '3.22.0',
			'current_options'       => [
				'cdn'        => 1,
				'cdn_type'   => 'rocketcdn',
				'cdn_cnames' => [ 'https://3c85d434.delivery.rocketcdn.me' ],
			],
			'cdn_state_from_bridge' => 'rocketcdn_free',
		],
		'expected' => [
			'options' => [
				'cdn'        => 1,
				'cdn_type'   => 'rocketcdn',
				'cdn_cnames' => [ 'https://3c85d434.delivery.rocketcdn.me' ],
				'cdn_state'  => 'rocketcdn_free',
			],
		],
	],

	// cdn_state already matches legacy fields (e.g. reconcile() ran first on < 3.22 path) — no save.
	'shouldSkipSaveWhenCdnStateAlreadyMatchesLegacyFields' => [
		'config'   => [
			'new_version'           => '3.23.4',
			'old_version'           => '3.21.0',
			'current_options'       => [
				'cdn'        => 1,
				'cdn_type'   => 'rocketcdn',
				'cdn_cnames' => [ 'https://3c85d434.delivery.rocketcdn.me' ],
				'cdn_state'  => 'rocketcdn_free',
			],
			'cdn_state_from_bridge' => 'rocketcdn_free',
		],
		'expected' => [
			'should_save' => false,
		],
	],

	// CNAME guard: cdn=1 + rocketcdn + no CNAME + active subscription means CDN was never
	// functional for this domain — legacy_to_state is bypassed and nothing is the target.
	'shouldSetNothingWhenRocketcdnEnabledButNoCnameSaved' => [
		'config'   => [
			'new_version'     => '3.23.4',
			'old_version'     => '3.22.0',
			'current_options' => [
				'cdn'        => 1,
				'cdn_type'   => 'rocketcdn',
				'cdn_cnames' => [],
			],
			'has_subscription'        => true,
			// cdn_state_from_bridge intentionally absent — legacy_to_state must not be called.
		],
		'expected' => [
			'options' => [
				'cdn'        => 1,
				'cdn_type'   => 'rocketcdn',
				'cdn_cnames' => [],
				'cdn_state'  => 'nothing',
			],
		],
	],

	'shouldSetNothingWhenCdnDisabledBelow3234'            => [
		'config'   => [
			'new_version'           => '3.23.4',
			'old_version'           => '3.21.0',
			'current_options'       => [
				'cdn'      => 0,
				'cdn_type' => 'rocketcdn',
			],
			'cdn_state_from_bridge' => 'nothing',
		],
		'expected' => [
			'options' => [
				'cdn'       => 0,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'nothing',
			],
		],
	],
];
