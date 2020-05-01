<?php

$content = require WP_ROCKET_TESTS_FIXTURES_DIR . '/content/advancedCacheContent.php';

return [
	'vfs_dir' => 'wp-content/',

	'settings' => [
		'cache_mobile'            => 0,
		'do_caching_mobile_files' => 0,
	],

	'test_data' => [
		[
			'settings' => [],
			'content'  => $content['non_mobile'],
		],
		[
			'settings' => [
				'cache_mobile' => 1,
			],
			'content'  => $content['non_mobile'],
		],
		[
			'settings' => [
				'do_caching_mobile_files' => 1,
			],
			'content'  => $content['non_mobile'],
		],
		[
			'settings' => [
				'cache_mobile'            => 1,
				'do_caching_mobile_files' => 1,
			],
			'content'  => $content['mobile'],
		],

		// When the file doesn't exist.
		[
			'settings' => [
				'cache_mobile'            => 1,
				'do_caching_mobile_files' => 1,
			],
			'content'  => $content['mobile'],
			true,
		],
	],
];
