<?php

return [
	[
		'url'       => 'http://example.org',
		'expected'  => 'http://example.org',
	],
	[
		'url'       => 'http://example.org?p=10',
		'expected'  => 'http://example.org?p=10',
	],
	[
		'url'       => 'https://example.org/lorem-ipsum/',
		'expected'  => 'https://example.org/lorem-ipsum/',
	],

	[
		'url'       => '//example.org',
		'expected'  => 'http://example.org',
	],
	[
		'url'       => '//example.org?p=10',
		'expected'  => 'http://example.org?p=10',
	],
	[
		'url'       => '//example.org/lorem-ipsum/',
		'expected'  => 'http://example.org/lorem-ipsum/',
	],

	[
		'url'       => 'example.org',
		'expected'  => 'http://example.org',
	],
	[
		'url'       => 'example.org?p=10',
		'expected'  => 'http://example.org?p=10',
	],
	[
		'url'       => 'example.org/lorem-ipsum/',
		'expected'  => 'http://example.org/lorem-ipsum/',
	],
];
