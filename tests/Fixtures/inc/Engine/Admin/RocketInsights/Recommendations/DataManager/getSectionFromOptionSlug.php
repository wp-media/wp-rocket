<?php
return [
	'test_data' => [
		'shouldReturnAdvancedCacheForUserCache' => [
			'config'   => [
				'option_slug' => 'cache_logged_user',
			],
			'expected' => 'advanced_cache',
		],

		'shouldReturnAddonsForVarnish'          => [
			'config'   => [
				'option_slug' => 'varnish_auto_purge',
			],
			'expected' => 'addons',
		],

		'shouldReturnMediaForImagify'           => [
			'config'   => [
				'option_slug' => 'plugin_imagify',
			],
			'expected' => 'media',
		],

		'shouldReturnDashboardForUnknownSlug'   => [
			'config'   => [
				'option_slug' => 'unknown_option',
			],
			'expected' => 'dashboard',
		],
	],
];
