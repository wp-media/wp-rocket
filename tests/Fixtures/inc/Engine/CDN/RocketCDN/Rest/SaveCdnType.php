<?php

return [
	'shouldSaveRocketcdnDriver'               => [
		'config'   => [
			'params'          => [ 'driver' => 'rocketcdn' ],
			'unauthenticated' => false,
		],
		'expected' => [
			'cdn_type_response' => 'rocketcdn',
		],
	],
	'shouldSaveByocdnDriver'                  => [
		'config'   => [
			'params'          => [ 'driver' => 'byocdn' ],
			'unauthenticated' => false,
		],
		'expected' => [
			'cdn_type_response' => 'byocdn',
		],
	],
	'shouldRejectInvalidDriver'               => [
		'config'   => [
			'params'          => [ 'driver' => 'invalid_driver' ],
			'unauthenticated' => false,
		],
		'expected' => [
			'code'   => 'rest_invalid_param',
			'status' => 400,
		],
	],
	'shouldReturnForbiddenWhenUnauthenticated' => [
		'config'   => [
			'params'          => [ 'driver' => 'rocketcdn' ],
			'unauthenticated' => true,
		],
		'expected' => [
			'code' => 'rest_forbidden',
		],
	],
];
