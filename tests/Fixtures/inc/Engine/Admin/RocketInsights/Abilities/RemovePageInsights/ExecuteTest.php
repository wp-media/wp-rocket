<?php

return [
	'testShouldReturnErrorWhenContextNotAllowed' => [
		'config'   => [
			'input'              => [
				'url' => 'https://example.com/page',
			],
			'context_is_allowed' => false,
		],
		'expected' => [
			'result' => [
				'success' => false,
				'error'   => 'Performance monitoring is disabled.',
			],
		],
	],

	'testShouldReturnErrorWhenUrlNotMonitored' => [
		'config'   => [
			'input'              => [
				'url' => 'https://example.com/not-monitored',
			],
			'context_is_allowed' => true,
			'query_rows'         => false,
		],
		'expected' => [
			'result' => [
				'success' => false,
				'error'   => 'URL is not currently being monitored.',
			],
		],
	],

	'testShouldRemoveSinglePage' => [
		'config'   => [
			'input'              => [
				'url' => 'https://example.com/page',
			],
			'context_is_allowed' => true,
			'query_rows'         => [ 5 ],
		],
		'expected' => [
			'deleted_ids' => [ 5 ],
			'result'      => [
				'success' => true,
				'error'   => '',
			],
		],
	],

	'testShouldRemoveBothMobileAndDesktopRows' => [
		'config'   => [
			'input'              => [
				'url' => 'https://example.com/page',
			],
			'context_is_allowed' => true,
			'query_rows'         => [ 5, 6 ],
		],
		'expected' => [
			'deleted_ids' => [ 5, 6 ],
			'result'      => [
				'success' => true,
				'error'   => '',
			],
		],
	],

	'testShouldAddProtocolToUrlWithoutProtocol' => [
		'config'   => [
			'input'              => [
				'url' => 'example.com/page',
			],
			'context_is_allowed' => true,
			'query_rows'         => [ 9 ],
		],
		'expected' => [
			'deleted_ids' => [ 9 ],
			'result'      => [
				'success' => true,
				'error'   => '',
			],
		],
	],
];
