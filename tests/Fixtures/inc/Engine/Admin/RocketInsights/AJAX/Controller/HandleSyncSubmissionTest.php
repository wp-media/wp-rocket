<?php

return [
	'test_data' => [
		'shouldFallbackToAsyncQueueWhenApiFailsNewUrl' => [
			'config' => [
				'url'                => 'http://example.org/test-page',
				'is_mobile'          => false,
				'additional_details' => [
					'title' => 'Test Page',
				],
			],
			'expected' => [
				'result_type' => 'int',
				'db_check'    => [
					'exists' => true,
					'status' => 'to-submit',
				],
			],
		],
		'shouldFallbackToAsyncQueueWhenApiFailsMobileUrl' => [
			'config' => [
				'url'                => 'http://example.org/mobile-test',
				'is_mobile'          => true,
				'additional_details' => [],
			],
			'expected' => [
				'result_type' => 'int',
				'db_check'    => [
					'exists' => true,
					'status' => 'to-submit',
				],
			],
		],
		'shouldHandleUrlWithQueryParams' => [
			'config' => [
				'url'                => 'http://example.org/page?param=value',
				'is_mobile'          => false,
				'additional_details' => [],
			],
			'expected' => [
				'result_type' => 'int',
				'db_check'    => [
					'exists' => true,
					'status' => 'to-submit',
				],
			],
		],
		'shouldHandleUrlWithFragment' => [
			'config' => [
				'url'                => 'http://example.org/page#section',
				'is_mobile'          => false,
				'additional_details' => [],
			],
			'expected' => [
				'result_type' => 'int',
				'db_check'    => [
					'exists' => true,
				],
			],
		],
		'shouldStoreAdditionalDetails' => [
			'config' => [
				'url'                => 'http://example.org/with-details',
				'is_mobile'          => false,
				'additional_details' => [
					'title'   => 'Page with Details',
					'is_home' => true,
				],
			],
			'expected' => [
				'result_type' => 'int',
				'db_check'    => [
					'exists' => true,
					'status' => 'to-submit',
				],
			],
		],
	],
];
