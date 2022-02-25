<?php

return [
	'shouldReturnDefaultWhenCombineJSDisabled' => [
		'config' => [
			'combine_js' => false,
			'user_cache' => false,
			'logged_in'  => false,
		],
		'expected' => [],
	],
	'shouldReturnDefaultUserCacheDisabled' => [
		'config' => [
			'combine_js' => true,
			'user_cache' => false,
			'logged_in'  => false,
		],
		'expected' => [],
	],
	'shouldReturnDefaultUserNotLoggedIn' => [
		'config' => [
			'combine_js' => true,
			'user_cache' => true,
			'logged_in'  => false,
		],
		'expected' => [],
	],
	'shouldReturnUpdated' => [
		'config' => [
			'combine_js' => true,
			'user_cache' => true,
			'logged_in'  => true,
		],
		'expected' => [
			'/wp-includes/js/dist/hooks(.min)?.js',
		],
	],
];
