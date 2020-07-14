<?php

return [
	'vfs_dir' => 'wp-content/',

	'structure' => [
		'wp-content' => [
			'plugins' => [
				'wp-rocket' => [
					'views' => [
						'cache' => [],
					],
				],
			],
		],
	],

	'test_data' => [
		[
			'expected' => [
				'vfs://public/wp-content/cache/wp-rocket/index.html',
				'vfs://public/wp-content/advanced-cache.php',
			]
		]
	]
];
