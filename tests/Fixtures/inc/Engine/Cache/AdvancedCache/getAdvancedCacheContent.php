<?php

$content = require WP_ROCKET_TESTS_FIXTURES_DIR . '/content/advancedCacheContent.php';

return [
	'vfs_dir' => 'wp-content/plugins/wp-rocket/views/cache/',

	'structure' => [
		'wp-content' => [
			'plugins'            => [
				'wp-rocket' => [
					'views' => [
						'cache' => [
							'advanced-cache.php' => file_get_contents( WP_ROCKET_PLUGIN_ROOT . 'views/cache/advanced-cache.php' ),
						],
					],
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
			'settings'                                => [],
			'expected'                                => $content['non_mobile'],
			'is_rocket_generate_caching_mobile_files' => false,
		],

		[
			'settings'                                => [
				'cache_mobile' => 1,
			],
			'expected'                                => $content['non_mobile'],
			'is_rocket_generate_caching_mobile_files' => false,
		],

		[
			'settings'                                => [
				'do_caching_mobile_files' => 1,
			],
			'expected'                                => $content['non_mobile'],
			'is_rocket_generate_caching_mobile_files' => false,
		],

		[
			'settings'                                => [
				'cache_mobile'            => 1,
				'do_caching_mobile_files' => 1,
			],
			'expected'                                => $content['mobile'],
			'is_rocket_generate_caching_mobile_files' => true,
		],
	],
];
