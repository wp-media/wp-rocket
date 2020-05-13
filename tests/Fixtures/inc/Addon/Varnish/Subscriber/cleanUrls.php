<?php
return [
	'bailoutShouldNotPurgeRocketVarnishFilter' => [
		[
			'varnish_auto_purge'           => true,
			'do_rocket_varnish_http_purge' => false,
		],
		[],
		[],
	],
	'bailoutShouldNotPurgeVanishOption' => [
		[
			'varnish_auto_purge'           => false,
			'do_rocket_varnish_http_purge' => true,
		],
		[],
		[],
	],
	'bailoutShouldNotPurgeBoth' => [
		[
			'varnish_auto_purge'           => false,
			'do_rocket_varnish_http_purge' => false,
		],
		[],
		[],
	],
	'bailoutShouldNotPurgeNoUrls' => [
		[
			'varnish_auto_purge'           => true,
			'do_rocket_varnish_http_purge' => true,
		],
		[],
		[],
	],
	'shoulNotPurgeVarnishQueryUrls' => [
		[
			'varnish_auto_purge'           => true,
			'do_rocket_varnish_http_purge' => true,
			'permalink_structure'          => '/%postname%/',
		],
		[
			[
				'home_url'  => 'http://example.org/home1',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home1',
				'logged_in' => true,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home1/#amp=',
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home1/best-source-of-gifs/#amp=',
				],
			],
		],
		[],
	],
	'shouldPurgeVarnishUrlsSlashedPermalink' => [
		[
			'varnish_auto_purge'           => true,
			'do_rocket_varnish_http_purge' => true,
			'permalink_structure'          => '/%postname%/',
		],
		[
			[
				'home_url'  => 'http://example.org/home1/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home1/',
				'logged_in' => true,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home1/how-to-prank-your-coworkers/',
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home1/best-source-of-gifs/',
				],
			],
		],
		[
			'http://example.org/home1/how-to-prank-your-coworkers/',
			'http://example.org/home1/best-source-of-gifs/',
		],
	],
	'shouldPurgeVarnishUrlsUnSlashedPermalink' => [
		[
			'varnish_auto_purge'           => true,
			'do_rocket_varnish_http_purge' => true,
			'permalink_structure'          => '/%postname%',
		],
		[
			[
				'home_url'  => 'http://example.org/home1',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home1',
				'logged_in' => true,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home1/how-to-prank-your-coworkers',
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home1/best-source-of-gifs',
				],
			],
		],
		[
			'http://example.org/home1/how-to-prank-your-coworkers/',
			'http://example.org/home1/best-source-of-gifs/',
		],
	],
	'shouldPurgeVarnishUrlSlashedPermalinkWrongSlashUnslash' => [
		[
			'varnish_auto_purge'           => true,
			'do_rocket_varnish_http_purge' => true,
			'permalink_structure'          => '/%postname%/',
		],
		[
			[
				'home_url'  => 'http://example.org/home1',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home1',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home1/abc',
				],
			],
			[
				'home_url'  => 'http://example.org/home2/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home2',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home2/abc',
				],
			],
			[
				'home_url'  => 'http://example.org/home3/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home3/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home3/abc',
				],
			],
			[
				'home_url'  => 'http://example.org/home4/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home4/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home4/abc/',
				],
			],
			[
				'home_url'  => 'http://example.org/home5/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home5',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home5/abc/',
				],
			],
			[
				'home_url'  => 'http://example.org/home6',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home6/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home6/abc/',
				],
			],
		],
		[
			'http://example.org/home1/abc/',
			'http://example.org/home2/abc/',
			'http://example.org/home3/abc/',
			'http://example.org/home4/abc/',
			'http://example.org/home5/abc/',
			'http://example.org/home6/abc/',
		],
	],
	'shouldPurgeVarnishUrlUnSlashedPermalinkWrongSlashUnslash' => [
		[
			'varnish_auto_purge'           => true,
			'do_rocket_varnish_http_purge' => true,
			'permalink_structure'          => '/%postname%',
		],
		[
			[
				'home_url'  => 'http://example.org/home1',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home1',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home1/abc',
				],
			],
			[
				'home_url'  => 'http://example.org/home2/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home2',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home2/abc',
				],
			],
			[
				'home_url'  => 'http://example.org/home3/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home3/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home3/abc',
				],
			],
			[
				'home_url'  => 'http://example.org/home4/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home4/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home4/abc/',
				],
			],
			[
				'home_url'  => 'http://example.org/home5/',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home5',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home5/abc/',
				],
			],
			[
				'home_url'  => 'http://example.org/home6',
				'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home6/',
				'logged_in' => false,
				'files'     => [
					'/path-to/home1/wp-content/cache/wp-rocket/example.org-Greg-594d03f6ae698691165999/home6/abc/',
				],
			],
		],
		[
			'http://example.org/home1/abc/',
			'http://example.org/home2/abc/',
			'http://example.org/home3/abc/',
			'http://example.org/home4/abc/',
			'http://example.org/home5/abc/',
			'http://example.org/home6/abc/',
		],
	],
];
