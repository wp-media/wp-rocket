<?php
return [
	'shouldPersistActiveDriver'                   => [
		'config'   => [
			'params'          => [ 'active_driver' => 'byocdn' ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'active_driver_response' => 'byocdn',
		],
	],
	'shouldPersistPausedStateAsInteger'           => [
		'config'   => [
			'params'          => [ 'paused' => 1 ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'paused_response' => 1,
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
			'active_driver_response' => 'builtin',
			'paused_response'        => 1,
		],
	],
	'shouldAcceptBuiltinDriver'                   => [
		'config'   => [
			'params'          => [ 'active_driver' => 'builtin' ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'active_driver_response' => 'builtin',
		],
	],
	'shouldAcceptByocdnDriver'                    => [
		'config'   => [
			'params'          => [ 'active_driver' => 'byocdn' ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'active_driver_response' => 'byocdn',
		],
	],
	'shouldAcceptRocketcdnDriver'                 => [
		'config'   => [
			'params'          => [ 'active_driver' => 'rocketcdn' ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'active_driver_response' => 'rocketcdn',
		],
	],
	'shouldRejectInvalidDriverValue'              => [
		'config'   => [
			'params'          => [ 'active_driver' => 'invalid_driver' ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'code' => 'rest_invalid_param',
		],
	],
	'shouldNotUpdateDriverWhenOnlyPausedIsSent'   => [
		'config'   => [
			'params'          => [ 'paused' => 0 ],
			'preset_options'  => [ 'rocketcdn_active_driver' => 'rocketcdn' ],
			'unauthenticated' => false,
		],
		'expected' => [
			'active_driver_response' => 'rocketcdn',
		],
	],
	'shouldReturnForbiddenWhenUnauthenticated'    => [
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
