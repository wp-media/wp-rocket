<?php
return [
	// -------------------------------------------------------------------------
	// < 3.22 path — sets cdn_type (and fixes cdn flag where needed)
	// -------------------------------------------------------------------------

	'shouldSetByocdnWhenLegacyCdnIsEnabled'               => [
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
			'options' => [
				'cdn'      => 1,
				'cdn_type' => 'byocdn',
			],
		],
	],

	'shouldSetRocketcdnWhenCdnIsNotEnabled'               => [
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

	// cdn disabled but CNAME present: ports to byocdn, preserves cdn=0.
	// Previous behaviour wrongly forced cdn=1 and defaulted to rocketcdn.
	'shouldSetByocdnWhenCdnDisabledButCnameExists'        => [
		'config'   => [
			'new_version'             => '3.22.0',
			'old_version'             => '3.21.1',
			'current_options'         => [ 'cdn' => 0 ],
			'has_active_subscription' => false,
			'cdn_cnames'              => [ 'https://cdnexample.org/' ],
		],
		'expected' => [
			'options' => [
				'cdn'      => 0,
				'cdn_type' => 'byocdn',
			],
		],
	],

	'shouldSetRocketcdnWhenHavingActiveSubscription'      => [
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

	// Active subscription + cdn already enabled: cdn=1 is preserved.
	'shouldPreserveCdnEnabledWithActiveSubscription'      => [
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
