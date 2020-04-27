<?php

$content = require __DIR__ . '/advancedCacheContent.php';

return [
	// Use in tests when the test data starts in this directory.
	'vfs_dir' => 'wp-content/',

	'structure' => [
		'wp-content'       => [
			'plugins' => [
				'wp-rocket' => [
					'inc'              => [
						'process-autoloader.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'inc/process-autoloader.php' ),
					],
					'licence-data.php' => '',
				],
			],
		],
		'wp-rocket-config' => [
			'example.org.php' => '<?php $var = "Some contents.";',
		],

		'advanced-cache.php' => '<?php $var = "Some contents.";',
	],

	'settings' => [
		'cache_mobile'            => 0,
		'do_caching_mobile_files' => 0,
	],

	'test_data' => [
		[
			'settings'                                => [],
			'expected'                                => $content['starting'] . $content['ending'],
			'is_rocket_generate_caching_mobile_files' => false,
		],
		[
			'settings'                                => [
				'cache_mobile' => 1,
			],
			'expected'                                => $content['starting'] . $content['ending'],
			'is_rocket_generate_caching_mobile_files' => false,
		],
		[
			'settings'                                => [
				'do_caching_mobile_files' => 1,
			],
			'expected'                                => $content['starting'] . $content['ending'],
			'is_rocket_generate_caching_mobile_files' => false,
		],
		[
			'settings'                                => [
				'cache_mobile'            => 1,
				'do_caching_mobile_files' => 1,
			],
			'expected'                                => $content['starting'] . $content['mobile'] . $content['ending'],
			'is_rocket_generate_caching_mobile_files' => true,
		],
	],
];
