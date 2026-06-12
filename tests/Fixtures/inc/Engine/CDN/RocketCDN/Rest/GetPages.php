<?php

return [
	'shouldReturnEmptyListWhenNoPagesRegistered'     => [
		'config'   => [
			'prefill'         => [],
			'unauthenticated' => false,
		],
		'expected' => [
			'pages' => [],
			'count' => 0,
			'limit' => 3,
		],
	],
	'shouldReturnAllRegisteredPagesWithMetadata'     => [
		'config'   => [
			'prefill'         => [
				[ 'url' => 'http://example.org/about', 'title' => 'About' ],
				[ 'url' => 'http://example.org/contact', 'title' => 'Contact' ],
			],
			'unauthenticated' => false,
		],
		'expected' => [
			'count'        => 2,
			'limit'        => 3,
			'pages_count'  => 2,
			'contains_urls' => [
				'http://example.org/about',
				'http://example.org/contact',
			],
		],
	],
	'shouldReturnForbiddenWhenUnauthenticated'       => [
		'config'   => [
			'prefill'         => [],
			'unauthenticated' => true,
		],
		'expected' => [
			'code' => 'rest_forbidden',
		],
	],
];
