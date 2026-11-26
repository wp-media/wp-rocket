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

	'testShouldQueryHttpsWhenSchemeIsHttpOnHttpsHome' => [
		'config'   => [
			'home_url'           => 'https://example.com',
			'input'              => [
				'url' => 'http://example.com/page',
			],
			'context_is_allowed' => true,
			'query_rows'         => [ 5 ],
		],
		'expected' => [
			'queried_url' => 'https://example.com/page',
			'deleted_ids' => [ 5 ],
			'result'      => [
				'success' => true,
				'error'   => '',
			],
		],
	],

	'testShouldQueryHttpsWhenUrlHasNoSchemeOnHttpsHome' => [
		'config'   => [
			'home_url'           => 'https://example.com',
			'input'              => [
				'url' => 'example.com/page',
			],
			'context_is_allowed' => true,
			'query_rows'         => [ 5 ],
		],
		'expected' => [
			'queried_url' => 'https://example.com/page',
			'deleted_ids' => [ 5 ],
			'result'      => [
				'success' => true,
				'error'   => '',
			],
		],
	],

	'testShouldKeepHttpsUrlOnHttpsHome' => [
		'config'   => [
			'home_url'           => 'https://example.com',
			'input'              => [
				'url' => 'https://example.com/page',
			],
			'context_is_allowed' => true,
			'query_rows'         => [ 5 ],
		],
		'expected' => [
			'queried_url' => 'https://example.com/page',
			'deleted_ids' => [ 5 ],
			'result'      => [
				'success' => true,
				'error'   => '',
			],
		],
	],

	'testShouldKeepHttpUrlOnHttpHome' => [
		'config'   => [
			'home_url'           => 'http://example.com',
			'input'              => [
				'url' => 'http://example.com/page',
			],
			'context_is_allowed' => true,
			'query_rows'         => [ 5 ],
		],
		'expected' => [
			'queried_url' => 'http://example.com/page',
			'deleted_ids' => [ 5 ],
			'result'      => [
				'success' => true,
				'error'   => '',
			],
		],
	],

	'testShouldKeepHttpUrlOfOtherHost' => [
		'config'   => [
			'home_url'           => 'https://example.com',
			'input'              => [
				'url' => 'http://other.com/page',
			],
			'context_is_allowed' => true,
			'query_rows'         => [ 5 ],
		],
		'expected' => [
			'queried_url' => 'http://other.com/page',
			'deleted_ids' => [ 5 ],
			'result'      => [
				'success' => true,
				'error'   => '',
			],
		],
	],
];
