<?php
return [
	'shouldPersistActiveDriver'                  => [
		'config'   => [
			'params'          => [ 'active_driver' => 'byocdn' ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'active_driver_option' => 'byocdn',
		],
	],
	'shouldPersistPausedStateAsInteger'          => [
		'config'   => [
			'params'          => [ 'paused' => 1 ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'paused_option' => 1,
		],
	],
	'shouldPersistBothActiveDriverAndPausedState' => [
		'config'   => [
			'params'          => [
				'active_driver' => 'builtin',
				'paused'        => 1,
			],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'active_driver_option' => 'builtin',
			'paused_option'        => 1,
		],
	],
	'shouldAcceptBuiltinDriver'                  => [
		'config'   => [
			'params'          => [ 'active_driver' => 'builtin' ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'active_driver_option' => 'builtin',
		],
	],
	'shouldAcceptByocdnDriver'                   => [
		'config'   => [
			'params'          => [ 'active_driver' => 'byocdn' ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'active_driver_option' => 'byocdn',
		],
	],
	'shouldAcceptRocketcdnDriver'                => [
		'config'   => [
			'params'          => [ 'active_driver' => 'rocketcdn' ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'active_driver_option' => 'rocketcdn',
		],
	],
	'shouldRejectInvalidDriverValue'             => [
		'config'   => [
			'params'          => [ 'active_driver' => 'invalid_driver' ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'code' => 'rest_invalid_param',
		],
	],
	'shouldNotUpdateDriverWhenOnlyPausedIsSent'  => [
		'config'   => [
			'params'          => [ 'paused' => 0 ],
			'preset_options'  => [ 'wpr_rocketcdn_active_driver' => 'byocdn' ],
			'unauthenticated' => false,
		],
		'expected' => [
			'active_driver_option' => 'byocdn',
		],
	],
	'shouldReturnForbiddenWhenUnauthenticated'   => [
		'config'   => [
			'params'          => [ 'active_driver' => 'rocketcdn' ],
			'preset_options'  => [],
			'unauthenticated' => true,
		],
		'expected' => [
			'code' => 'rest_forbidden',
		],
	],
];
