<?php

return [
	'shouldRemovePageAndReturnUpdatedList'        => [
		'config'   => [
			'prefill' => [
				[ 'url' => 'http://example.org/about', 'title' => 'About' ],
			],
			'delete'  => 'http://example.org/about',
		],
		'expected' => [
			'count' => 0,
			'pages' => [],
		],
	],
	'shouldRemoveCorrectPageWhenMultipleExist'    => [
		'config'   => [
			'prefill' => [
				[ 'url' => 'http://example.org/remove-me', 'title' => 'Remove Me' ],
				[ 'url' => 'http://example.org/keep-me', 'title' => 'Keep Me' ],
			],
			'delete'  => 'http://example.org/remove-me',
		],
		'expected' => [
			'count'         => 1,
			'remaining_url' => 'http://example.org/keep-me',
		],
	],
	'shouldReturn404ForNonExistentId'             => [
		'config'   => [
			'prefill' => [],
			'delete'  => 'nonexistent',
		],
		'expected' => [
			'code'   => 'rocketcdn_page_not_found',
			'status' => 404,
		],
	],
	'shouldReturnForbiddenWhenUnauthenticated'    => [
		'config'   => [
			'prefill' => [],
			'delete'  => 'unauthenticated',
		],
		'expected' => [
			'code' => 'rest_forbidden',
		],
	],
];
