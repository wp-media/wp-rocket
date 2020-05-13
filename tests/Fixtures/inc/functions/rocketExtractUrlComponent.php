<?php

return [
	[
		'url'       => 'https://example.org/lorem-ipsum/',
		'component' => PHP_URL_SCHEME,
		'expected'  => 'https',
	],
	[
		'url'       => 'http://example.org/lorem-ipsum/',
		'component' => PHP_URL_HOST,
		'expected'  => 'example.org',
	],
	[
		'url'       => 'http://example.org/lorem-ipsum/',
		'component' => PHP_URL_PATH,
		'expected'  => '/lorem-ipsum/',
	],
	[
		'url'       => 'http://example.org/lorem-ipsum',
		'component' => PHP_URL_PATH,
		'expected'  => '/lorem-ipsum',
	],
	[
		'url'       => 'http://example.org/2020/03/lorem-ipsum/',
		'component' => PHP_URL_PATH,
		'expected'  => '/2020/03/lorem-ipsum/',
	],
	[
		'url'       => 'http://example.org/lorem-ipsum/nec-ullamcorper',
		'component' => PHP_URL_PATH,
		'expected'  => '/lorem-ipsum/nec-ullamcorper',
	],
	[
		'url'       => 'https://example.org/nec-ullamcorper',
		'component' => -1,
		'expected'  => [
			'scheme' => 'https',
			'host'   => 'example.org',
			'path'   => '/nec-ullamcorper',
		],
	],
];
