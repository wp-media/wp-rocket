<?php

return [
	'shouldPersistPausedStateAsOneForRocketcdn'  => [
		'config'   => [
			'params'          => [ 'paused' => 1, 'driver' => 'rocketcdn' ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'paused_response' => 1,
		],
	],
	'shouldPersistPausedStateAsZeroForRocketcdn' => [
		'config'   => [
			'params'          => [ 'paused' => 0, 'driver' => 'rocketcdn' ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'paused_response' => 0,
		],
	],
	'shouldPersistPausedStateAsOneForByocdn'     => [
		'config'   => [
			'params'          => [ 'paused' => 1, 'driver' => 'byocdn' ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'paused_response' => 1,
		],
	],
	'shouldPersistPausedStateAsZeroForByocdn'    => [
		'config'   => [
			'params'          => [ 'paused' => 0, 'driver' => 'byocdn' ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'paused_response' => 0,
		],
	],
	'shouldReturnForbiddenWhenUnauthenticated'   => [
		'config'   => [
			'params'          => [ 'paused' => 1, 'driver' => 'rocketcdn' ],
			'preset_options'  => [],
			'unauthenticated' => true,
		],
		'expected' => [
			'code' => 'rest_forbidden',
		],
	],
];
