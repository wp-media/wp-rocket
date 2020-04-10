<?php

return [
	'shouldBailOutWhenNotAString' => [
		'url'       => false,
		'expected'  => null,
	],
	[
		'url'      => 'https://example.org/lorem-ipsum/',
		'expected' => [
			'host'     => 'example.org',
			'path'     => '/lorem-ipsum/',
			'scheme'   => 'https',
			'query'    => '',
			'fragment' => '',
		],
	],
	[
		'url'      => 'http://example.org/lorem-ipsum/',
		'expected' => [
			'host'     => 'example.org',
			'path'     => '/lorem-ipsum/',
			'scheme'   => 'http',
			'query'    => '',
			'fragment' => '',
		],
	],
	[
		'url'      => 'http://example.org/lorem-ipsum',
		'expected' => [
			'host'     => 'example.org',
			'path'     => '/lorem-ipsum',
			'scheme'   => 'http',
			'query'    => '',
			'fragment' => '',
		],
	],
	[
		'url'      => 'http://example.org/lorem-ipsum?amp',
		'expected' => [
			'host'     => 'example.org',
			'path'     => '/lorem-ipsum',
			'scheme'   => 'http',
			'query'    => 'amp',
			'fragment' => '',
		],
	],
	[
		'url'      => 'http://example.org/2020/03/lorem-ipsum/',
		'expected' => [
			'host'     => 'example.org',
			'path'     => '/2020/03/lorem-ipsum/',
			'scheme'   => 'http',
			'query'    => '',
			'fragment' => '',
		],
	],
	[
		'url'      => 'https://Example.org/lorem-ipsum/nec-ullamcorper',
		'expected' => [
			'host'     => 'example.org',
			'path'     => '/lorem-ipsum/nec-ullamcorper',
			'scheme'   => 'https',
			'query'    => '',
			'fragment' => '',
		],
	],
	[
		'url'      => 'https://Example.org/lorem-ipsum/nec-ullamcorper?foo=bar#baz',
		'expected' => [
			'host'     => 'example.org',
			'path'     => '/lorem-ipsum/nec-ullamcorper',
			'scheme'   => 'https',
			'query'    => 'foo=bar',
			'fragment' => 'baz',
		],
	],
];
