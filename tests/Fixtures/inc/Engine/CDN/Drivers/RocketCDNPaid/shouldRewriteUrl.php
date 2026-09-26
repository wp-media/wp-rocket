<?php

return [
	'testShouldReturnTrueWhenNoExcludedPages'              => [
		'config'   => [
			'url'            => 'https://example.com/page/',
			'cdn_urls'       => [ 'cdn.example.com' ],
			'excluded_pages' => [],
		],
		'expected' => true,
	],
	'testShouldReturnTrueWhenUrlDoesNotMatchAnyExcludedPage' => [
		'config'   => [
			'url'            => 'https://example.com/page/',
			'cdn_urls'       => [ 'cdn.example.com' ],
			'excluded_pages' => [ '/other-page', '/contact' ],
		],
		'expected' => true,
	],
	'testShouldReturnFalseWhenUrlMatchesExactExcludedPage' => [
		'config'   => [
			'url'            => 'https://example.com/shop',
			'cdn_urls'       => [ 'cdn.example.com' ],
			'excluded_pages' => [ '/shop' ],
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenUrlMatchesSubstringPattern'  => [
		'config'   => [
			'url'            => 'https://example.com/product/item',
			'cdn_urls'       => [ 'cdn.example.com' ],
			'excluded_pages' => [ '/product' ],
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenForcedOff'                   => [
		'config'   => [
			'url'           => 'https://example.com/page/',
			'cdn_urls'      => [ 'cdn.example.com' ],
			'is_forced_off' => true,
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenNoHostnameConfigured'        => [
		'config'   => [
			'url'      => 'https://example.com/page/',
			'cdn_urls' => [],
		],
		'expected' => false,
	],
];
