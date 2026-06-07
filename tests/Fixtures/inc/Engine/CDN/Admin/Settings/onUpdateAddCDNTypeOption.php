<?php
return [
	'shouldBailWhenOldVersionIsGteUpgradeVersion'       => [
		'config'   => [
			'new_version' => '3.22.0',
			'old_version' => '3.22.0',
		],
		'expected' => [
			'should_update' => false,
		],
	],
	'shouldSetByocdnWhenLegacyCdnIsEnabled'             => [
		'config'   => [
			'new_version'             => '3.22.0',
			'old_version'             => '3.21.1',
			'current_options'         => [
				'cdn' => 1,
			],
			'has_active_subscription' => false,
			'cdn_cnames'              => [
				'https://cdnexample.org/',
			],
		],
		'expected' => [
			'should_update' => true,
			'cdn_type'      => 'byocdn',
			'options'       => [
				'cdn'      => 1,
				'cdn_type' => 'byocdn',
			],
		],
	],
	'shouldSetRocketcdnWhenNoCnames'                    => [
		'config'   => [
			'new_version'             => '3.22.0',
			'old_version'             => '3.21.1',
			'current_options'         => [],
			'has_active_subscription' => false,
			'cdn_cnames'              => [],
		],
		'expected' => [
			'should_update' => true,
			'cdn_type'      => 'rocketcdn',
			'options'       => [
				'cdn_type' => 'rocketcdn',
			],
		],
	],
	'shouldSetRocketcdnWhenHavingActiveSubscription'    => [
		'config'   => [
			'new_version'             => '3.22.0',
			'old_version'             => '3.21.1',
			'current_options'         => [
				'cdn' => 1,
			],
			'has_active_subscription' => true,
		],
		'expected' => [
			'should_update' => true,
			'cdn_type'      => 'rocketcdn',
			'options'       => [
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			],
		],
	],
	'shouldSetByocdnWhenCdnDisabledButCnameExists'      => [
		'config'   => [
			'new_version'             => '3.22.0',
			'old_version'             => '3.21.1',
			'current_options'         => [ 'cdn' => 0 ],
			'has_active_subscription' => false,
			'cdn_cnames'              => [ 'https://cdnexample.org/' ],
		],
		'expected' => [
			'should_update' => true,
			'cdn_type'      => 'byocdn',
			'options'       => [
				'cdn'      => 0,
				'cdn_type' => 'byocdn',
			],
		],
	],
];
