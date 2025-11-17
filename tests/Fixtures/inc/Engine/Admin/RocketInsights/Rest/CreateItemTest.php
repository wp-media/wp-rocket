<?php
use WP_Rocket\Tests\Fixtures\Generators\UserDataGenerator;

return [
	'testShouldAddPageSuccessfully'                      => [
		'config'   => [
			'post_data'     => [
				'page_url' => 'http://example.org/test-page',
				'source'   => 'dashboard',
			],
			'rows'          => [
				[
					'url'       => 'http://example.org',
					'status'    => 'completed',
					'is_mobile' => 1,
				],
			],
			'customer_data' => ( new UserDataGenerator() ),
			'mock_http'     => true,
		],
		'expected' => [
			'code'             => 200,
			'success'          => true,
			'database_entries' => 2,
			'hook_fired'       => true,
			'response_data'    => [
				'id'                => null, // Will be generated
				'html'              => null, // Will be generated
				'global_score_data' => null, // Will be generated
				'remaining_urls'    => null, // Will be generated
				'can_add_pages'     => true,
			],
		],
	],
	'testShouldFailWithEmptyUrl'                         => [
		'config'   => [
			'post_data'     => [
				'page_url' => '',
				'source'   => 'dashboard',
			],
			'rows'          => [
				[
					'url'       => 'http://example.org',
					'status'    => 'completed',
					'is_mobile' => 1,
				],
			],
			'customer_data' => ( new UserDataGenerator() ),
			'mock_http'     => false,
		],
		'expected' => [
			'code'          => 400,
			'error_message' => 'Invalid parameter(s): page_url',
		],
	],
	'testShouldFailWithInvalidUrl'                       => [
		'config'   => [
			'post_data'     => [
				'page_url' => 'invalid-url-format',
				'source'   => 'dashboard',
			],
			'rows'          => [
				[
					'url'       => 'http://example.org',
					'status'    => 'completed',
					'is_mobile' => 1,
				],
			],
			'customer_data' => ( new UserDataGenerator() ),
			'mock_http'     => false,
		],
		'expected' => [
			'code'          => 400,
			'error_message' => 'Invalid parameter(s): page_url',
		],
	],
	'testShouldFailWithUrlLimitReached'                  => [
		'config'   => [
			'post_data'     => [
				'page_url' => 'https://example.com/test-page',
				'source'   => 'dashboard',
			],
			'rows'          => [
				[
					'url'       => 'http://example.org',
					'status'    => 'completed',
					'is_mobile' => 1,
				],
				[
					'url'       => 'http://example.org/page2',
					'status'    => 'completed',
					'is_mobile' => 1,
				],
				[
					'url'       => 'http://example.org/page3',
					'status'    => 'completed',
					'is_mobile' => 1,
				],
			],
			'customer_data' => ( new UserDataGenerator() ),
			'mock_http'     => false,
		],
		'expected' => [
			'code' => 403,
			'error_message' => 'reached your free limit',
		],
	],
	'testShouldFailWithUnreachableUrl'                   => [
		'config'   => [
			'post_data'     => [
				'page_url' => 'https://external-site.com/page',
				'source'   => 'dashboard',
			],
			'rows'          => [
				[
					'url'       => 'http://example.org',
					'status'    => 'completed',
					'is_mobile' => 1,
				],
			],
			'customer_data' => ( new UserDataGenerator() ),
			'mock_http'     => true,
		],
		'expected' => [
			'code'          => 400,
			'error_message' => 'Url does not resolve to a valid page',
		],
	],
	'testShouldSucceedWithExternalUrl'                   => [
		'config'   => [
			'post_data'     => [
				'page_url' => 'https://example.org',
				'source'   => 'dashboard',
			],
			'rows'          => [],
			'customer_data' => ( new UserDataGenerator() ),
			'mock_http'     => true,
		],
		'expected' => [
			'code'             => 200,
			'success'          => true,
			'database_entries' => 1,
			'hook_fired'       => true,
			'response_data'    => [
				'id'                => null, // Will be generated
				'html'              => null, // Will be generated
				'global_score_data' => null, // Will be generated
				'remaining_urls'    => null, // Will be generated
				'can_add_pages'     => true,
			],
		],
	],
	'testShouldStoreUrlWithNonLatinCharactersInCyrillic' => [
		'config'   => [
			'post_data'     => [
				'page_url' => 'http://example.org/new-page-with-special-char-%d0%bf%d1%80%d0%be%d0%b4%d1%83%d0%ba%d1%82%d0%be%d0%b2%d0%b0-%d0%ba%d0%b0%d1%82%d0%b5%d0%b3%d0%be%d1%80%d0%b8%d1%8f',
				'source'   => 'dashboard',
			],
			'rows'          => [],
			'customer_data' => ( new UserDataGenerator() ),
			'mock_http'     => true,
		],
		'expected' => [
			'code'                 => 200,
			'success'              => true,
			'database_entries'     => 1,
			'hook_fired'           => true,
			'verify_url_in_db'     => true,
			'expected_url_pattern' => '/%d0%bf%d1%80%d0%be%d0%b4%d1%83%d0%ba%d1%82%d0%be%d0%b2%d0%b0/',
			'response_data'        => [
				'id'                => null,
				'html'              => null,
				'global_score_data' => null,
				'remaining_urls'    => null,
				'can_add_pages'     => true,
			],
		],
	],
	'testShouldStoreUrlWithNonLatinCharactersInArabic'   => [
		'config'   => [
			'post_data'     => [
				'page_url' => 'http://example.org/category/%d8%ba%d9%8a%d8%b1-%d9%85%d8%b5%d9%86%d9%81',
				'source'   => 'dashboard',
			],
			'rows'          => [],
			'customer_data' => ( new UserDataGenerator() ),
			'mock_http'     => true,
		],
		'expected' => [
			'code'                 => 200,
			'success'              => true,
			'database_entries'     => 1,
			'hook_fired'           => true,
			'verify_url_in_db'     => true,
			'expected_url_pattern' => '/%d8%ba%d9%8a%d8%b1/',
			'response_data'        => [
				'id'                => null,
				'html'              => null,
				'global_score_data' => null,
				'remaining_urls'    => null,
				'can_add_pages'     => true,
			],
		],
	],
	'testShouldStoreUrlWithOnlyNonLatinPath'             => [
		'config'   => [
			'post_data'     => [
				'page_url' => 'http://example.org/%D0%BF%D1%80%D0%BE%D0%B4%D1%83%D0%BA%D1%82%D0%BE%D0%B2%D0%B0/3',
				'source'   => 'dashboard',
			],
			'rows'          => [],
			'customer_data' => ( new UserDataGenerator() ),
			'mock_http'     => true,
		],
		'expected' => [
			'code'                 => 200,
			'success'              => true,
			'database_entries'     => 1,
			'hook_fired'           => true,
			'verify_url_in_db'     => true,
			'expected_url_pattern' => '/%D0%BF%D1%80%D0%BE%D0%B4%D1%83%D0%BA%D1%82%D0%BE%D0%B2%D0%B0/',
			'response_data'        => [
				'id'                => null,
				'html'              => null,
				'global_score_data' => null,
				'remaining_urls'    => null,
				'can_add_pages'     => true,
			],
		],
	],
	'testShouldStoreUrlWithMixedLatinAndNonLatin'        => [
		'config'   => [
			'post_data'     => [
				'page_url' => 'http://example.org/page-%E4%B8%AD%E6%96%87-test',
				'source'   => 'dashboard',
			],
			'rows'          => [],
			'customer_data' => ( new UserDataGenerator() ),
			'mock_http'     => true,
		],
		'expected' => [
			'code'                 => 200,
			'success'              => true,
			'database_entries'     => 1,
			'hook_fired'           => true,
			'verify_url_in_db'     => true,
			'expected_url_pattern' => '/%E4%B8%AD%E6%96%87/',
			'response_data'        => [
				'id'                => null,
				'html'              => null,
				'global_score_data' => null,
				'remaining_urls'    => null,
				'can_add_pages'     => true,
			],
		],
	],
	'testShouldPreventExceedingLimitAfterInsertion'      => [
		'config'   => [
			'post_data'     => [
				'page_url' => 'http://example.org/test-page-4',
				'source'   => 'dashboard',
			],
			'rows'          => [
				[
					'url'       => 'http://example.org',
					'status'    => 'completed',
					'is_mobile' => 1,
				],
				[
					'url'       => 'http://example.org/page2',
					'status'    => 'completed',
					'is_mobile' => 1,
				],
				[
					'url'       => 'http://example.org/page3',
					'status'    => 'to-submit',
					'is_mobile' => 1,
				],
			],
			'customer_data' => ( new UserDataGenerator() ),
			'mock_http'     => true,
		],
		'expected' => [
			'code'             => 403,
			'error_message'    => 'reached your free limit',
			'database_entries' => 3, // Should stay at 3, the 4th should be deleted
		],
	],
	'testShouldAllowAddingUrlAtExactLimit'               => [
		'config'   => [
			'post_data'     => [
				'page_url' => 'http://example.org/third-page',
				'source'   => 'dashboard',
			],
			'rows'          => [
				[
					'url'       => 'http://example.org',
					'status'    => 'completed',
					'is_mobile' => 1,
				],
				[
					'url'       => 'http://example.org/page2',
					'status'    => 'completed',
					'is_mobile' => 1,
				],
			],
			'customer_data' => ( new UserDataGenerator() ),
			'mock_http'     => true,
		],
		'expected' => [
			'code'             => 200,
			'success'          => true,
			'database_entries' => 3, // Should successfully add the 3rd URL (at the limit)
			'hook_fired'       => true,
			'response_data'    => [
				'id'                => null,
				'html'              => null,
				'global_score_data' => null,
				'remaining_urls'    => 0, // Should be 0 remaining after adding 3rd of 3
				'can_add_pages'     => false, // Can't add more pages after reaching limit
			],
		],
	],
	'testShouldRollbackWhenRaceConditionExceedsLimit'    => [
		'config'   => [
			'post_data'          => [
				'page_url' => 'http://example.org/page-over-limit',
				'source'   => 'dashboard',
			],
			'rows'               => [
				[
					'url'       => 'http://example.org',
					'status'    => 'completed',
					'is_mobile' => 1,
				],
			],
			'customer_data'      => ( new UserDataGenerator() )->with_custom_limit( 2 ),
			'mock_http'          => true,
			'add_concurrent_url' => true, // Will add another URL mid-request to simulate race condition
		],
		'expected' => [
			'code'             => 403,
			'error_message'    => 'reached your free limit',
			'database_entries' => 2, // Should rollback to 2 (the concurrent one + original, not the failed one)
		],
	],
];
