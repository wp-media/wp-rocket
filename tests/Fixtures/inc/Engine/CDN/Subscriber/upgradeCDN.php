<?php
return [
	// -------------------------------------------------------------------------
	// < 3.22 path — sets cdn_type
	// -------------------------------------------------------------------------

	// CDN enabled with CNAME and no RocketCDN sub → byocdn.
	'shouldSetByocdnWhenLegacyCdnIsEnabled'                        => [
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
			'cdn_enabled'             => 1,
		],
		'expected' => [
			'options' => [
				'cdn'      => 1,
				'cdn_type' => 'byocdn',
			],
		],
	],

	// No CNAME, no sub → rocketcdn, cdn forced on.
	'shouldSetRocketcdnWhenCdnIsNotEnabled'                        => [
		'config'   => [
			'new_version'             => '3.22.0',
			'old_version'             => '3.21.1',
			'current_options'         => [],
			'has_active_subscription' => false,
		],
		'expected' => [
			'options' => [
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			],
		],
	],

	// CNAME present but cdn disabled → is_cdn_enabled() returns false, byocdn condition fails.
	// cdn_type defaults to rocketcdn and cdn is forced on.
	'shouldSetRocketcdnWhenCdnDisabledButCnameExists'              => [
		'config'   => [
			'new_version'             => '3.22.0',
			'old_version'             => '3.21.1',
			'current_options'         => [ 'cdn' => 0 ],
			'has_active_subscription' => false,
			'cdn_cnames'              => [ 'https://cdnexample.org/' ],
			'cdn_enabled'             => 0,
		],
		'expected' => [
			'options' => [
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			],
		],
	],

	// Active sub → rocketcdn, cdn not forced.
	'shouldSetRocketcdnWhenHavingActiveSubscription'               => [
		'config'   => [
			'new_version'             => '3.22.0',
			'old_version'             => '3.21.1',
			'current_options'         => [],
			'has_active_subscription' => true,
		],
		'expected' => [
			'options' => [
				'cdn_type' => 'rocketcdn',
			],
		],
	],

	// Active sub + cdn already on → cdn=1 preserved, rocketcdn.
	'shouldPreserveCdnEnabledWithActiveSubscription'               => [
		'config'   => [
			'new_version'             => '3.22.0',
			'old_version'             => '3.21.1',
			'current_options'         => [ 'cdn' => 1 ],
			'has_active_subscription' => true,
		],
		'expected' => [
			'options' => [
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			],
		],
	],
];
