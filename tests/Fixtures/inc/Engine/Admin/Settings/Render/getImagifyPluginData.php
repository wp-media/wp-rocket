<?php

$cached_data = (object) [
	'slug'            => 'imagify',
	'active_installs' => 1000000,
];

$api_data = (object) [
	'slug'            => 'imagify',
	'active_installs' => 2000000,
];

return [
	'testShouldReturnCachedDataWhenTransientExists' => [
		'config'   => [
			'transient'  => $cached_data,
			'api_result' => null,
			'is_error'   => false,
		],
		'expected' => [
			'calls_api' => false,
			'saved'     => null,
			'data'      => $cached_data,
		],
	],
	'testShouldFetchAndCacheDataWhenTransientIsMissing' => [
		'config'   => [
			'transient'  => false,
			'api_result' => $api_data,
			'is_error'   => false,
		],
		'expected' => [
			'calls_api' => true,
			'saved'     => $api_data,
			'data'      => $api_data,
		],
	],
	'testShouldReturnEmptyArrayWhenApiFails' => [
		'config'   => [
			'transient'  => false,
			'api_result' => new stdClass(),
			'is_error'   => true,
		],
		'expected' => [
			'calls_api' => true,
			'saved'     => [],
			'data'      => [],
		],
	],
];
