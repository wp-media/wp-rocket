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
		[
			'http://example.com/home1',
			'http://example.com/home2/',
			'http://example.com/home3/',
			'http://example.com/home4/',
			'http://example.com/home5/',
			'http://example.com/home6',
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
		[
			'http://example.com/home1',
			'http://example.com/home2/',
			'http://example.com/home3/',
			'http://example.com/home4/',
			'http://example.com/home5/',
			'http://example.com/home6',
		],
	],
];
