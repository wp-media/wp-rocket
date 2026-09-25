<?php

return [
	'testShouldReturnTrueForEmptyUrlWithHostname'    => [
		'config'   => [
			'url'      => '',
			'cdn_urls' => [ 'cdn.example.com' ],
		],
		'expected' => true,
	],
	'testShouldReturnTrueForRelativeUrlWithHostname' => [
		'config'   => [
			'url'      => '/some/page/',
			'cdn_urls' => [ 'cdn.example.com' ],
		],
		'expected' => true,
	],
	'testShouldReturnTrueForAbsoluteUrlWithHostname' => [
		'config'   => [
			'url'      => 'https://example.com/some/page/',
			'cdn_urls' => [ 'cdn.example.com' ],
		],
		'expected' => true,
	],
	'testShouldReturnFalseWhenNoHostnameConfigured'  => [
		'config'   => [
			'url'      => 'https://example.com/some/page/',
			'cdn_urls' => [],
		],
		'expected' => false,
	],
];
