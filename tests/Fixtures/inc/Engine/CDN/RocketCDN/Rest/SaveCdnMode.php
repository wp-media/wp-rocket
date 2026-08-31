<?php

return [
	'shouldSaveRocketcdnFreeMode'             => [
		'config'   => [
			'params'          => [ 'mode' => 'rocketcdn_free' ],
			'unauthenticated' => false,
		],
		'expected' => [
			'cdn_state_response' => 'rocketcdn',
			'cdn'                => 1,
			'cdn_type'           => 'rocketcdn',
		],
	],
	'shouldSaveRocketcdnPaidMode'             => [
		'config'   => [
			'params'          => [ 'mode' => 'rocketcdn_paid' ],
			'unauthenticated' => false,
		],
		'expected' => [
			'cdn_state_response' => 'rocketcdn',
			'cdn'                => 1,
			'cdn_type'           => 'rocketcdn',
		],
	],
	'shouldSaveByocdnMode'                    => [
		'config'   => [
			'params'          => [ 'mode' => 'byocdn' ],
			'unauthenticated' => false,
		],
		'expected' => [
			'cdn_state_response' => 'byocdn',
			'cdn'                => 1,
			'cdn_type'           => 'byocdn',
		],
	],
	'shouldSaveNothingMode'                   => [
		'config'   => [
			'params'          => [ 'mode' => 'nothing' ],
			'unauthenticated' => false,
		],
		'expected' => [
			'cdn_state_response' => 'nothing',
			'cdn'                => 0,
			'cdn_type'           => 'rocketcdn',
		],
	],
	'shouldRejectInvalidMode'                 => [
		'config'   => [
			'params'          => [ 'mode' => 'invalid_mode' ],
			'unauthenticated' => false,
		],
		'expected' => [
			'code'   => 'rest_invalid_param',
			'status' => 400,
		],
	],
	'shouldReturnForbiddenWhenUnauthenticated' => [
		'config'   => [
			'params'          => [ 'mode' => 'rocketcdn_free' ],
			'unauthenticated' => true,
		],
		'expected' => [
			'code' => 'rest_forbidden',
		],
	],
];
