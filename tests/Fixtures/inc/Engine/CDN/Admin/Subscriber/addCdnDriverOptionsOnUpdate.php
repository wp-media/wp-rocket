<?php

return [
	'settings'  => [
		'cdn' => 0,
	],
	'test_data' => [
		'shouldAddOptionsWhenOldVersionIsBelow322'                        => [
			'config'   => [
				'new_version' => '3.22.0',
				'old_version' => '3.21.9',
			],
			'expected' => [
				'byocdn'    => 1,
				'rocketcdn' => 1,
			],
		],
		'shouldNotModifyOptionsWhenOldVersionEquals322'                   => [
			'config'   => [
				'new_version' => '3.23.0',
				'old_version' => '3.22.0',
			],
			'expected' => [
				'no_change' => true,
			],
		],
		'shouldNotModifyOptionsWhenOldVersionIsAbove322'                  => [
			'config'   => [
				'new_version' => '3.23.0',
				'old_version' => '3.22.1',
			],
			'expected' => [
				'no_change' => true,
			],
		],
		'shouldSetRocketcdnToZeroWhenCdnDisabledAndHasActiveSubscription' => [
			'config'   => [
				'new_version'             => '3.22.0',
				'old_version'             => '3.21.9',
				'cdn_enabled'             => false,
				'has_active_subscription' => true,
			],
			'expected' => [
				'byocdn'    => 1,
				'rocketcdn' => 0,
			],
		],
		'shouldSetByocdnToZeroWhenCdnDisabledAndHasCnames'               => [
			'config'   => [
				'new_version'             => '3.22.0',
				'old_version'             => '3.21.9',
				'cdn_enabled'             => false,
				'has_active_subscription' => false,
				'cdn_cnames'              => [ 'https://cdn.example.org/' ],
			],
			'expected' => [
				'byocdn'    => 0,
				'rocketcdn' => 1,
			],
		],
		'shouldKeepBothEnabledWhenCdnIsEnabled'                          => [
			'config'   => [
				'new_version'             => '3.22.0',
				'old_version'             => '3.21.9',
				'cdn_enabled'             => true,
				'has_active_subscription' => false,
				'cdn_cnames'              => [],
			],
			'expected' => [
				'byocdn'    => 1,
				'rocketcdn' => 1,
			],
		],
	],
];
