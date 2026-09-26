<?php

return [
	'testShouldReturnTrueWhenUrlIsFoundInDatabase'     => [
		'config'   => [
			'url'      => 'https://example.com/page/',
			'cdn_urls' => [ 'cdn.example.com' ],
			'is_found' => true,
		],
		'expected' => true,
	],
	'testShouldReturnFalseWhenUrlIsNotFoundInDatabase' => [
		'config'   => [
			'url'      => 'https://example.com/page/',
			'cdn_urls' => [ 'cdn.example.com' ],
			'is_found' => false,
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenForcedOff'               => [
		'config'   => [
			'url'           => 'https://example.com/page/',
			'cdn_urls'      => [ 'cdn.example.com' ],
			'is_forced_off' => true,
			'is_found'      => true,
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenNoHostnameConfigured'    => [
		'config'   => [
			'url'      => 'https://example.com/page/',
			'cdn_urls' => [],
		],
		'expected' => false,
	],
];
