<?php

return [
	'testShouldReturnFalseWhenNoLicense' => [
		'config'   => [
			'licence'           => true,
			'filter'            => true,
			'is_logged_in'      => false,
			'cache_logged_user' => 0,
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenFilterFalse' => [
		'config'   => [
			'licence'           => false,
			'filter'            => false,
			'is_logged_in'      => false,
			'cache_logged_user' => 0,
		],
		'expected' => false,
	],
	'testShouldReturnTrueWhenLicenseAndFilterTrue' => [
		'config'   => [
			'licence'           => false,
			'filter'            => true,
			'is_logged_in'      => false,
			'cache_logged_user' => 0,
		],
		'expected' => true,
	],
	'testShouldReturnFalseWhenLoggedInAndUserCacheEnabled' => [
		'config'   => [
			'licence'           => false,
			'filter'            => true,
			'is_logged_in'      => true,
			'cache_logged_user' => 1,
		],
		'expected' => false,
	],
	'testShouldReturnTrueWhenLoggedInButUserCacheDisabled' => [
		'config'   => [
			'licence'           => false,
			'filter'            => true,
			'is_logged_in'      => true,
			'cache_logged_user' => 0,
		],
		'expected' => true,
	],
	'testShouldReturnTrueWhenNotLoggedInAndUserCacheEnabled' => [
		'config'   => [
			'licence'           => false,
			'filter'            => true,
			'is_logged_in'      => false,
			'cache_logged_user' => 1,
		],
		'expected' => true,
	],
];
