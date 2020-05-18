<?php
return [
	'bailoutNoManualPreload' => [
		'/%postname%/',
		false,
		[],
		[],
	],
	'bailoutNoDeleted' => [
		'/%postname%/',
		true,
		[],
		[],
	],
	'bailoutAllUrlsHaveLoggedIn' => [
		'/%postname%/',
		true,
		[
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
		[],
	],
	'preloadUrlsSlashedPermalink' => [
		'/%postname%/',
		true,
		[
			[
				'home_url'  => 'http://example.com/home1',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1',
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1/abc',
				],
			],
			[
				'home_url'  => 'http://example.com/home2/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home2',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home2',
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home2/abc',
				],
			],
			[
				'home_url'  => 'http://example.com/home3/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home3/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home3',
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home3/abc',
				],
			],
			[
				'home_url'  => 'http://example.com/home4/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home4/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home4/',
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home4/abc/',
				],
			],
			[
				'home_url'  => 'http://example.com/home5/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home5',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home5/',
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home5/abc/',
				],
			],
			[
				'home_url'  => 'http://example.com/home6',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home6/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home6/',
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home6/abc/',
				],
			],
		],
		[
			'http://example.com/home1/',
			'http://example.com/home1/abc/',
			'http://example.com/home2/',
			'http://example.com/home2/abc/',
			'http://example.com/home3/',
			'http://example.com/home3/abc/',
			'http://example.com/home4/',
			'http://example.com/home4/abc/',
			'http://example.com/home5/',
			'http://example.com/home5/abc/',
			'http://example.com/home6/',
			'http://example.com/home6/abc/',
		],
	],
	'preloadUrlsUnSlashedPermalink' => [
		'/%postname%',
		true,
		[
			[
				'home_url'  => 'http://example.com/home1',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1',
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1/abc',
				],
			],
			[
				'home_url'  => 'http://example.com/home2/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home2',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home2',
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home2/abc',
				],
			],
			[
				'home_url'  => 'http://example.com/home3/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home3/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home3',
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home3/abc',
				],
			],
			[
				'home_url'  => 'http://example.com/home4/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home4/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home4/',
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home4/abc',
				],
			],
			[
				'home_url'  => 'http://example.com/home5/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home5',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home5/',
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home5/abc',
				],
			],
			[
				'home_url'  => 'http://example.com/home6',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home6/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home6/',
					'/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home6/abc/',
				],
			],
		],
		[
			'http://example.com/home1',
			'http://example.com/home1/abc',
			'http://example.com/home2',
			'http://example.com/home2/abc',
			'http://example.com/home3',
			'http://example.com/home3/abc',
			'http://example.com/home4',
			'http://example.com/home4/abc',
			'http://example.com/home5',
			'http://example.com/home5/abc',
			'http://example.com/home6',
			'http://example.com/home6/abc',
		],
	],
];
