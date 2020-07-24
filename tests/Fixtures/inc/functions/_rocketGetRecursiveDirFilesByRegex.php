<?php

return [
	'vfs_dir' => 'wp-content/cache/wp-rocket/',

	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.org' => [
						'folder_1' => [
							'folder_1_1' => [
								'file_1_1_1.htm' => '',
								'file_1_1_2.htm' => '',
								'file_1_1_3.htm' => ''
							],
							'folder_1_2' => [
								'file_1_2_1.php' => '',
								'file_1_2_2.htm' => '',
								'file_1_3_3.type' => '',
								'folder_1_2_1' => [
									'file_1_2_1_1.htm' => '',
									'file_1_2_1_2.htm' => '',
									'file_1_2_1_3.htm' => ''
								]
							],
						],
						'folder_2' => [
							'folder_2_1' => [
								'file_2_1_1.type' => '',
								'file_2_1_2.type' => '',
								'file_2_1_3.type' => ''
							]
						],
						'folder_3' => []
					]
				]
			]

		]
	],

	'test_data' => [
		'shouldGetFilesMultilevel' => [
			'config' => [
				'regex' => '/.htm$/'
			],
			'expected' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org/folder_1/folder_1_1/file_1_1_1.htm',
				'vfs://public/wp-content/cache/wp-rocket/example.org/folder_1/folder_1_1/file_1_1_2.htm',
				'vfs://public/wp-content/cache/wp-rocket/example.org/folder_1/folder_1_1/file_1_1_3.htm',
				'vfs://public/wp-content/cache/wp-rocket/example.org/folder_1/folder_1_2/file_1_2_2.htm',
				'vfs://public/wp-content/cache/wp-rocket/example.org/folder_1/folder_1_2/folder_1_2_1/file_1_2_1_1.htm',
				'vfs://public/wp-content/cache/wp-rocket/example.org/folder_1/folder_1_2/folder_1_2_1/file_1_2_1_2.htm',
				'vfs://public/wp-content/cache/wp-rocket/example.org/folder_1/folder_1_2/folder_1_2_1/file_1_2_1_3.htm'
			]
		],
		'shouldGetFilesOneFolder' => [
			'config' => [
				'regex' => '/^.+folder_2.+\\.type/i'
			],
			'expected' => [
				'vfs://public/wp-content/cache/wp-rocket/example.org/folder_2/folder_2_1/file_2_1_1.type',
				'vfs://public/wp-content/cache/wp-rocket/example.org/folder_2/folder_2_1/file_2_1_2.type',
				'vfs://public/wp-content/cache/wp-rocket/example.org/folder_2/folder_2_1/file_2_1_3.type'
			]
		],
		'shouldGetEmptyFolder' => [
			'config' => [
				'regex' => '/^.+folder_3.+\\.type/i'
			],
			'expected' => []
		],
	]
];
