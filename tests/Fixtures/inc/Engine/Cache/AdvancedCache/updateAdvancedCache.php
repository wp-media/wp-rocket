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

		'shouldBailOutWhenShortCircuitFilterSet' => [
			'set_filter' => true,
			'expected'   => [ 'vfs://public/wp-content/cache/wp-rocket/index.html' ],
		],

		'shouldWriteAdvancedCacheWhenNotPrevented' => [
			'set_filter' => false,
			'expected'   => [
				'vfs://public/wp-content/cache/wp-rocket/index.html',
				'vfs://public/wp-content/advanced-cache.php',
			]
		]
	]
];
