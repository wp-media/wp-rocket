<?php

$content = require __DIR__ . '/advancedCacheContent.php';

return [
	'vfs_dir' => 'wp-content/',

	'structure' => [
		'wp-content' => [
			'plugins'            => [
				'wp-rocket' => [
					'inc'              => [
						'process-autoloader.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'inc/process-autoloader.php' ),
					],
					'licence-data.php' => '',
				],
			],
			'advanced-cache.php' => '',
		],
	],

	'settings' => [
		'cache_mobile'            => 0,
		'do_caching_mobile_files' => 0,
	],

	'test_data' => [
		[
			'settings' => [],
			'content'  => $content['starting'] . $content['ending'],
		],
		[
			'settings' => [
				'cache_mobile' => 1,
			],
			'content'  => $content['starting'] . $content['ending'],
		],
		[
			'settings' => [
				'do_caching_mobile_files' => 1,
			],
			'content'  => $content['starting'] . $content['ending'],
		],
		[
			'settings' => [
				'cache_mobile'            => 1,
				'do_caching_mobile_files' => 1,
			],
			'content'  => $content['starting'] . $content['mobile'] . $content['ending'],
		],

		// When the file doesn't exist.
		[
			'settings' => [
				'cache_mobile'            => 1,
				'do_caching_mobile_files' => 1,
			],
			'content'  => $content['starting'] . $content['mobile'] . $content['ending'],
			true,
		],
	],
];
