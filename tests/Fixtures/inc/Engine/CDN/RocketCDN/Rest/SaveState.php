<?php

return [
	'shouldPersistPausedStateAsOne'              => [
		'config'   => [
			'params'          => [ 'paused' => 1 ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'paused_response' => 1,
		],
	],
	'shouldPersistPausedStateAsZero'             => [
		'config'   => [
			'params'          => [ 'paused' => 0 ],
			'preset_options'  => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'paused_response' => 0,
		],
	],
	'shouldReturnForbiddenWhenUnauthenticated'   => [
		'config'   => [
			'params'          => [ 'paused' => 1 ],
			'preset_options'  => [],
			'unauthenticated' => true,
		],
		'expected' => [
			'code' => 'rest_forbidden',
		],
	],
];
