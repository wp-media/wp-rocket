<?php

$files = [
	'vfs://public/wp-content/cache/used-css/1/',
	'vfs://public/wp-content/cache/used-css/1/'.md5( 'https://example.org/' ).'/',
	'vfs://public/wp-content/cache/used-css/1/'.md5( 'https://example.org/' ).'/used.css',
	'vfs://public/wp-content/cache/used-css/1/'.md5( 'https://example.org/' ).'/used-mobile.css',
	'vfs://public/wp-content/cache/used-css/1/category/',
	'vfs://public/wp-content/cache/used-css/1/category/level1/',
	'vfs://public/wp-content/cache/used-css/1/category/level1/used.css',
	'vfs://public/wp-content/cache/used-css/1/category/level1/used-mobile.css',
];

return [

	'vfs_dir' => 'wp-content/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'used-css' => [
					1 => [
						md5( 'https://example.org/' ) => [
							'used.css' => '',
							'used-mobile.css' => '',
						],
						'category' => [
							'level1' => [
								'used.css' => '',
								'used-mobile.css' => '',
							]
						]
					],
				],
			],
		],
	],

	'test_data' => [
		'BailoutWhenCurrentUserCant' => [
			'input' => [
				'cap' => false,
				'old_value' => [
					'async_css' => true,
				],
				'new_value' => [
					'async_css' => true,
				],
				'files' => $files,
			],
			'expected' => [
				'cleaned' => false,
				'reason' => 'cap',
			],
		],

		'BailoutWhenRUCSSDisabled' => [
			'input' => [
				'cap' => true,
				'remove_unused_css' => false,
				'old_value' => [
					'async_css' => true,
				],
				'new_value' => [
					'async_css' => true,
				],
				'files' => $files,
			],
			'expected' => [
				'cleaned' => false,
				'reason' => 'option',
			],
		],

		'BailoutWhenCPCSSNotInsideOldValue' => [
			'input' => [
				'cap' => true,
				'remove_unused_css' => true,
				'old_value' => [],
				'new_value' => [
					'async_css' => true,
				],
				'files' => $files,
			],
			'expected' => [
				'cleaned' => false,
				'reason' => 'cpcss',
			],
		],

		'BailoutWhenCPCSSNotInsideNewValue' => [
			'input' => [
				'cap' => true,
				'remove_unused_css' => true,
				'old_value' => [
					'async_css' => true,
				],
				'new_value' => [],
				'files' => $files,
			],
			'expected' => [
				'cleaned' => false,
				'reason' => 'cpcss'
			],
		],

		'BailoutWhenCPCSSChangedFromDisabledToEnabled' => [
			'input' => [
				'cap' => true,
				'remove_unused_css' => true,
				'old_value' => [
					'async_css' => false,
				],
				'new_value' => [
					'async_css' => true,
				],
				'files' => $files
			],
			'expected' => [
				'cleaned' => false,
				'reason' => 'cpcss'
			],
		],

		'CleanWhenCPCSSChangedFromEnabledToDisabled' => [
			'input' => [
				'cap' => true,
				'remove_unused_css' => true,
				'old_value' => [
					'async_css' => true,
				],
				'new_value' => [
					'async_css' => false,
				],
				'files' => $files
			],
			'expected' => [
				'cleaned' => true,
			],
		],

	],

];
