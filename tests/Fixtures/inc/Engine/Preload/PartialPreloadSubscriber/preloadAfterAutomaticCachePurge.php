<?php
return [
	'bailoutNoManualPreload'     => [
		'manual_preload_option' => false,
		'deleted'               => [],
		'expected'              => [],
	],
	'bailoutNoDeleted'           => [
		'manual_preload_option' => true,
		'deleted'               => [],
		'expected'              => [],
	],
	'bailoutAllUrlsHaveLoggedIn' => [
		'manual_preload_option' => true,
		'deleted'               => [
			[
				'home_url'  => 'http://example.com/home1',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1',
				'logged_in' => true,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1/how-to-prank-your-coworkers',
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1/best-source-of-gifs',
				],
			],
		],
		'expected'              => [],
	],

	'shouldConvert_withOutTrailingslash_forIIS'             => [
		'manual_preload_option' => true,
		'deleted'               => [
			[
				'home_url'  => 'http://example.com/home1',
				'home_path' => 'C:\path-to\home1\wp-content\cache\wp-rocket\example.com-tester-594d03f6ae698691165999\home1\\',
				'logged_in' => false,
				'files'     => [
					'C:\path-to\home1\wp-content\cache\wp-rocket\example.com-tester-594d03f6ae698691165999\home1\\',
				],
			],
		],
		'expected'              => [
			'http://example.com/home1',
		],
	],
	'shouldConvert_withTrailingslash_forIIS'                => [
		'manual_preload_option' => true,
		'deleted'               => [
			[
				'home_url'  => 'http://example.com/home1/',
				'home_path' => 'C:\path-to\home1\wp-content\cache\wp-rocket\example.com-tester-594d03f6ae698691165999\home1',
				'logged_in' => false,
				'files'     => [
					'C:\path-to\home1\wp-content\cache\wp-rocket\example.com-tester-594d03f6ae698691165999\home1',
				],
			],
		],
		'expected'              => [
			'http://example.com/home1/',
		],
	],
	'shouldConvert_withTrailingslash_forIIS_withMismatches' => [
		'manual_preload_option' => true,
		'deleted'               => [
			[
				'home_url'  => 'http://example.com/home1/',
				'home_path' => 'C:\path-to\home1\wp-content\cache/wp-rocket/example.com-tester-594d03f6ae698691165999/home1',
				'logged_in' => false,
				'files'     => [
					'C:\path-to\home1\wp-content\cache/wp-rocket/example.com-tester-594d03f6ae698691165999/home1',
				],
			],
		],
		'expected'              => [
			'http://example.com/home1/',
		],
	],
	'shouldConvert_withOutTrailingslash_forLinux'           => [
		'manual_preload_option' => true,
		'deleted'               => [
			[
				'home_url'  => 'http://example.com/home1',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-tester-594d03f6ae698691165999/home1',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-tester-594d03f6ae698691165999/home1',
				],
			],
		],
		'expected'              => [
			'http://example.com/home1',
		],
	],
	'shouldConvert_withTrailingslash_forLinux'              => [
		'manual_preload_option' => true,
		'deleted'               => [
			[
				'home_url'  => 'http://example.com/home1/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-tester-594d03f6ae698691165999/home1',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-tester-594d03f6ae698691165999/home1',
				],
			],
		],
		'expected'              => [
			'http://example.com/home1/',
		],
	],
	'shouldConvert_withTrailingslash_forLinux_withmismatch' => [
		'manual_preload_option' => true,
		'deleted'               => [
			[
				'home_url'  => 'http://example.com/home1/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-tester-594d03f6ae698691165999/home1',
				'logged_in' => false,
				'files'     => [
					'path-to/home1/wp-content/cache/wp-rocket/example.com-tester-594d03f6ae698691165999/home1/',
				],
			],
		],
		'expected'              => [
			'http://example.com/home1/',
		],
	],

	'shouldConvertUrls_withTrailingSlash_forLinux' => [
		'manual_preload_option' => true,
		'deleted'               => [
			[
				'home_url'  => 'http://example.com/home1',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1',
				],
			],
			[
				'home_url'  => 'http://example.com/home2/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home2',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home2',
				],
			],
			[
				'home_url'  => 'http://example.com/home3/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home3/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home3',
				],
			],
			[
				'home_url'  => 'http://example.com/home4/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home4/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home4/',
				],
			],
			[
				'home_url'  => 'http://example.com/home5/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home5',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home5/',
				],
			],
			[
				'home_url'  => 'http://example.com/home6',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home6/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home6/',
				],
			],
		],
		'expected'              => [
			'http://example.com/home1',
			'http://example.com/home2/',
			'http://example.com/home3/',
			'http://example.com/home4/',
			'http://example.com/home5/',
			'http://example.com/home6',
		],
	],
	'preloadUrlsUnSlashedPermalink'                => [
		'manual_preload_option' => true,
		'deleted'               => [
			[
				'home_url'  => 'http://example.com/home1',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1',
				],
			],
			[
				'home_url'  => 'http://example.com/home2/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home2',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home2',
				],
			],
			[
				'home_url'  => 'http://example.com/home3/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home3/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home3',
				],
			],
			[
				'home_url'  => 'http://example.com/home4/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home4/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home4/',
				],
			],
			[
				'home_url'  => 'http://example.com/home5/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home5',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home5/',
				],
			],
			[
				'home_url'  => 'http://example.com/home6',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home6/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home6/',
				],
			],
		],
		'expected'              => [
			'http://example.com/home1',
			'http://example.com/home2/',
			'http://example.com/home3/',
			'http://example.com/home4/',
			'http://example.com/home5/',
			'http://example.com/home6',
		],
	],
	'shouldConvertUrls_withTrailingSlash_forIIS'   => [
		'manual_preload_option' => true,
		'deleted'               => [
			[
				'home_url'  => 'http://example.com/home1',
				'home_path' => 'C:\path-to\home1\wp-content\cache\wp-rocket\example.com\home1',
				'logged_in' => false,
				'files'     => [
					'C:\path-to\home1\wp-content\cache\wp-rocket\example.com\home1',
				],
			],
			[
				'home_url'  => 'http://example.com/home2/',
				'home_path' => 'C:\path-to\home1\wp-content\cache\wp-rocket\example.com\home2',
				'logged_in' => false,
				'files'     => [
					'C:\path-to\home1\wp-content\cache\wp-rocket\example.com\home2',
				],
			],
			[
				'home_url'  => 'http://example.com/home3/',
				'home_path' => 'C:\path-to\home1\wp-content\cache/wp-rocket/example.com-tester-594d03f6ae698691165999/home3/',
				'logged_in' => false,
				'files'     => [
					'C:\path-to\home1\wp-content\cache/wp-rocket/example.com-tester-594d03f6ae698691165999/home3/',
				],
			],
			[
				'home_url'  => 'http://example.com/fr/home4/',
				'home_path' => 'C:\path-to\home1\wp-content\cache\wp-rocket\example.com-tester2-123456\fr\home4\\',
				'logged_in' => false,
				'files'     => [
					'C:\path-to\home1\wp-content\cache\wp-rocket\example.com-tester2-123456\fr\home4',
				],
			],
		],
		'expected'              => [
			'http://example.com/home1',
			'http://example.com/home2/',
			'http://example.com/home3/',
			'http://example.com/fr/home4/',
		],
	],
];
