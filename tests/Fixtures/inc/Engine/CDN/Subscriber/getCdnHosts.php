<?php
return [
	// Empty CDN list.
	'testWithEmptyCDNList' => [
		'config' => [
			'original' => [],
			'zones' => [ 'all' ],
			'cdn_urls' => [],
		],
		'expected' => [],
	],
	// CDN list + original host.
	'testWithCDNListAndOriginalHost' => [
		'config' => [
			'original' => [ 'cdn5.example.org' ],
			'zones' => [ 'all' ],
			'cdn_urls' => [
				'http://cdn.example.org',
				'//cdn2.example.org',
				'https://cdn3.example.org',
				'cdn4.example.org',
			],
		],
		'expected' => [
			'cdn5.example.org',
			'cdn.example.org',
			'cdn2.example.org',
			'cdn3.example.org',
			'cdn4.example.org',
		],
	],
	// CDN list with invalid URL, duplicate entries, URL with path.
	'testWithInvalidUrl' => [
		'config' => [
			'original' => [],
			'zones' => [ 'all' ],
			'cdn_urls' => [
				'http://cdn.example.org/path',
				'//cdn2.example.org',
				'//cdn2.example.org',
				'/subdir/',
				'https://cdn3.example.org/path/subdir/',
			],
		],
		'expected' => [
			'cdn.example.org/path',
			'cdn2.example.org',
			'cdn3.example.org/path/subdir',
		],
	],
];
