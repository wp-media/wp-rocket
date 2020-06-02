<?php
return [
	'vfs_dir' => 'wp-content/cache/wp-rocket/',
	'structure' => [
		'wp-content' => [
			'cache' => [
				'wp-rocket' => [
					'example.com'                => [
						'index.html' => '',
						'feed' => [
							'index.html' => ''
						],
						'comments' => [
							'feed' => [
								'index.html' => ''
							],
						],
					],
					'example.com-wpmedia-123456' => [
						'index.html' => '',
						'feed' => [
							'index.html' => ''
						],
						'comments' => [
							'feed' => [
								'index.html' => ''
							],
						]
					],

					'baz.example.com'             => [
						'index.html' => '',
						'feed' => [
							'index.html' => ''
						],
						'comments' => [
							'feed' => [
								'index.html' => ''
							],
						]
					],
					'baz.example.com-baz1-123456' => [
						'index.html' => '',
						'feed' => [
							'index.html' => ''
						],
						'comments' => [
							'feed' => [
								'index.html' => ''
							],
						]
					],

					'wp.baz.example.com'               => [
						'index.html' => '',
						'feed' => [
							'index.html' => ''
						],
						'comments' => [
							'feed' => [
								'index.html' => ''
							],
						]
					],
					'wp.baz.example.com-wpbaz1-123456' => [
						'index.html' => '',
						'feed' => [
							'index.html' => ''
						],
						'comments' => [
							'feed' => [
								'index.html' => ''
							],
						]
					],

					'example.com#fr' => [
						'index.html' => '',
						'feed' => [
							'index.html' => ''
						],
						'comments' => [
							'feed' => [
								'index.html' => ''
							],
						]
					],
				],
			],
		],
	],
	'test_data' => [
		'testShouldRemoveFilesForMainDomain' => [
			'config' => [
				'home_url' => 'http://example.com',
			],
			'expected' => [
				'removed_files' => [
					'example.com/feed',
					'example.com/comments/feed',
					'example.com/comments/feed/index.html',
					'example.com/feed/index.html',

					'example.com-wpmedia-123456/feed',
					'example.com-wpmedia-123456/feed/index.html',
					'example.com-wpmedia-123456/comments/feed',
					'example.com-wpmedia-123456/comments/feed/index.html',

					'example.com#fr/feed',
					'example.com#fr/feed/index.html',
					'example.com#fr/comments/feed',
					'example.com#fr/comments/feed/index.html',
				],
				'not_removed_files' => [
					'example.com/index.html',
					'example.com-wpmedia-123456/index.html',
					'example.com#fr/index.html',

					'baz.example.com/index.html',
					'baz.example.com/feed',
					'baz.example.com/feed/index.html',
					'baz.example.com/comments/feed',
					'baz.example.com/comments/feed/index.html',

					'baz.example.com-baz1-123456/index.html',
					'baz.example.com-baz1-123456/feed',
					'baz.example.com-baz1-123456/feed/index.html',
					'baz.example.com-baz1-123456/comments/feed',
					'baz.example.com-baz1-123456/comments/feed/index.html',

					'wp.baz.example.com/index.html',
					'wp.baz.example.com/feed',
					'wp.baz.example.com/feed/index.html',
					'wp.baz.example.com/comments/feed',
					'wp.baz.example.com/comments/feed/index.html',

					'wp.baz.example.com-wpbaz1-123456/index.html',
					'wp.baz.example.com-wpbaz1-123456/feed',
					'wp.baz.example.com-wpbaz1-123456/feed/index.html',
					'wp.baz.example.com-wpbaz1-123456/comments/feed',
					'wp.baz.example.com-wpbaz1-123456/comments/feed/index.html',
				],
			]
		],
		'testShouldRemoveFilesForSubDomain' => [
			'config' => [
				'home_url' => 'http://baz.example.com',
			],
			'expected' => [
				'removed_files' => [
					'baz.example.com/feed',
					'baz.example.com/feed/index.html',
					'baz.example.com/comments/feed',
					'baz.example.com/comments/feed/index.html',

					'baz.example.com-baz1-123456/feed',
					'baz.example.com-baz1-123456/feed/index.html',
					'baz.example.com-baz1-123456/comments/feed',
					'baz.example.com-baz1-123456/comments/feed/index.html',
				],
				'not_removed_files' => [
					'example.com/index.html',
					'example.com-wpmedia-123456/index.html',
					'example.com#fr/index.html',

					'example.com/feed',
					'example.com/comments/feed',
					'example.com/comments/feed/index.html',
					'example.com/feed/index.html',

					'example.com-wpmedia-123456/feed',
					'example.com-wpmedia-123456/feed/index.html',
					'example.com-wpmedia-123456/comments/feed',
					'example.com-wpmedia-123456/comments/feed/index.html',

					'example.com#fr/feed',
					'example.com#fr/feed/index.html',
					'example.com#fr/comments/feed',
					'example.com#fr/comments/feed/index.html',

					'baz.example.com/index.html',
					'baz.example.com-baz1-123456/index.html',

					'wp.baz.example.com/index.html',
					'wp.baz.example.com/feed',
					'wp.baz.example.com/feed/index.html',
					'wp.baz.example.com/comments/feed',
					'wp.baz.example.com/comments/feed/index.html',

					'wp.baz.example.com-wpbaz1-123456/index.html',
					'wp.baz.example.com-wpbaz1-123456/feed',
					'wp.baz.example.com-wpbaz1-123456/feed/index.html',
					'wp.baz.example.com-wpbaz1-123456/comments/feed',
					'wp.baz.example.com-wpbaz1-123456/comments/feed/index.html',
				],
			]
		],
		'testShouldRemoveFilesForSubSubDomain' => [
			'config' => [
				'home_url' => 'http://wp.baz.example.com',
			],
			'expected' => [
				'removed_files' => [
					'wp.baz.example.com/feed',
					'wp.baz.example.com/feed/index.html',
					'wp.baz.example.com/comments/feed',
					'wp.baz.example.com/comments/feed/index.html',

					'wp.baz.example.com-wpbaz1-123456/feed',
					'wp.baz.example.com-wpbaz1-123456/feed/index.html',
					'wp.baz.example.com-wpbaz1-123456/comments/feed',
					'wp.baz.example.com-wpbaz1-123456/comments/feed/index.html',
				],
				'not_removed_files' => [
					'example.com/index.html',
					'example.com-wpmedia-123456/index.html',
					'example.com#fr/index.html',

					'example.com/feed',
					'example.com/comments/feed',
					'example.com/comments/feed/index.html',
					'example.com/feed/index.html',

					'example.com-wpmedia-123456/feed',
					'example.com-wpmedia-123456/feed/index.html',
					'example.com-wpmedia-123456/comments/feed',
					'example.com-wpmedia-123456/comments/feed/index.html',

					'example.com#fr/feed',
					'example.com#fr/feed/index.html',
					'example.com#fr/comments/feed',
					'example.com#fr/comments/feed/index.html',

					'baz.example.com/index.html',
					'baz.example.com/feed',
					'baz.example.com/feed/index.html',
					'baz.example.com/comments/feed',
					'baz.example.com/comments/feed/index.html',

					'baz.example.com-baz1-123456/index.html',
					'baz.example.com-baz1-123456/feed',
					'baz.example.com-baz1-123456/feed/index.html',
					'baz.example.com-baz1-123456/comments/feed',
					'baz.example.com-baz1-123456/comments/feed/index.html',

					'wp.baz.example.com/index.html',
					'wp.baz.example.com-wpbaz1-123456/index.html'
				],
			]
		]
	],
];
