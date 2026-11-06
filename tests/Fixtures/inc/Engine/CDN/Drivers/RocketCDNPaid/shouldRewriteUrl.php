<?php

return [
	'testShouldReturnTrueWhenNoExcludedPages'               => [
		'config'   => [
			'url'            => 'https://example.com/page/',
			'excluded_pages' => [],
		],
		'expected' => true,
	],
	'testShouldReturnTrueWhenUrlDoesNotMatchAnyExcludedPage' => [
		'config'   => [
			'url'            => 'https://example.com/page/',
			'excluded_pages' => [ '/other-page', '/contact' ],
		],
		'expected' => true,
	],
	'testShouldReturnFalseWhenUrlMatchesExactExcludedPage'  => [
		'config'   => [
			'url'            => 'https://example.com/shop',
			'excluded_pages' => [ '/shop' ],
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenUrlMatchesSubstringPattern'   => [
		'config'   => [
			'url'            => 'https://example.com/product/item',
			'excluded_pages' => [ '/product' ],
		],
		'expected' => false,
	],
];