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
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			],
			'cdn_state_from_bridge' => 'rocketcdn_free',
		],
		'expected' => [
			'options' => [
				'cdn'       => 1,
				'cdn_type'  => 'rocketcdn',
				'cdn_state' => 'rocketcdn_free',
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
