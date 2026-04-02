<?php

return [
	'testShouldReturnErrorWhenEnvironmentIsLocal' => [
		'config'   => [
			'input'            => [
				'url' => 'https://example.com/page',
			],
			'environment_type' => 'local',
		],
		'expected' => [
			'result' => [
				'success' => false,
				'error'   => 'Performance monitoring is disabled for local environment',
			],
		],
	],

	'testShouldReturnErrorWhenContextNotAllowed' => [
		'config'   => [
			'input'              => [
				'url' => 'https://example.com/page',
			],
			'environment_type'   => 'production',
			'context_is_allowed' => false,
		],
		'expected' => [
			'result' => [
				'success' => false,
				'error'   => 'Performance monitoring is disabled.',
			],
		],
	],

	'testShouldReturnErrorWhenUrlDoesNotResolve' => [
		'config'   => [
			'input'              => [
				'url' => 'https://example.com/page',
			],
			'environment_type'   => 'production',
			'context_is_allowed' => true,
			'http_response'      => [
				'response'    => [],
				'is_error'    => true,
				'status_code' => 0,
				'body'        => '',
			],
		],
		'expected' => [
			'result' => [
				'success' => false,
				'error'   => 'Url does not resolve to a valid page.',
			],
		],
	],

	'testShouldReturnErrorWhenHttpStatusIsNot200' => [
		'config'   => [
			'input'              => [
				'url' => 'https://example.com/page',
			],
			'environment_type'   => 'production',
			'context_is_allowed' => true,
			'http_response'      => [
				'response'    => [],
				'is_error'    => false,
				'status_code' => 404,
				'body'        => '',
			],
		],
		'expected' => [
			'result' => [
				'success' => false,
				'error'   => 'Url does not resolve to a valid page.',
			],
		],
	],

	'testShouldReturnErrorWhenUrlIsAdminPage' => [
		'config'   => [
			'input'              => [
				'url' => 'https://example.com/wp-admin/options.php',
			],
			'environment_type'   => 'production',
			'context_is_allowed' => true,
			'admin_url'          => 'https://example.com/wp-admin/',
			'http_response'      => [
				'response'    => [],
				'is_error'    => false,
				'status_code' => 200,
				'body'        => '<html><head><title>Admin Page</title></head></html>',
			],
		],
		'expected' => [
			'result' => [
				'success' => false,
				'error'   => 'Url is an admin page.',
			],
		],
	],

	'testShouldReturnErrorWhenUrlAlreadySubmitted' => [
		'config'   => [
			'input'                  => [
				'url' => 'https://example.com/page',
			],
			'environment_type'       => 'production',
			'context_is_allowed'     => true,
			'admin_url'              => 'https://example.com/wp-admin/',
			'http_response'          => [
				'response'    => [],
				'is_error'    => false,
				'status_code' => 200,
				'body'        => '<html><head><title>Test Page</title></head></html>',
			],
			'manager_get_single_job' => [
				'id'  => 1,
				'url' => 'https://example.com/page',
			],
		],
		'expected' => [
			'result' => [
				'success' => false,
				'error'   => '',
			],
		],
	],

	'testShouldReturnErrorWhenDbInsertFailsAfterSyncSuccess' => [
		'config'   => [
			'input'                        => [
				'url' => 'https://example.com/page',
			],
			'environment_type'             => 'production',
			'context_is_allowed'           => true,
			'admin_url'                    => 'https://example.com/wp-admin/',
			'home_url'                     => 'https://example.com/',
			'http_response'                => [
				'response'    => [],
				'is_error'    => false,
				'status_code' => 200,
				'body'        => '<html><head><title>Test Page</title></head></html>',
			],
			'job_processor_send_api_result' => [
				'uuid' => 'test-uuid-123',
			],
			'manager_add_to_queue_result'  => false,
		],
		'expected' => [
			'job_processor_send_api_called' => true,
			'manager_add_to_queue_called'   => true,
			'result'                        => [
				'success' => false,
				'error'   => 'Failed to add page to monitoring queue.',
			],
		],
	],

	'testShouldFallbackToAsyncAndReturnErrorWhenSyncFailsAndDbInsertFails' => [
		'config'   => [
			'input'                         => [
				'url' => 'https://example.com/page',
			],
			'environment_type'              => 'production',
			'context_is_allowed'            => true,
			'admin_url'                     => 'https://example.com/wp-admin/',
			'home_url'                      => 'https://example.com/',
			'http_response'                 => [
				'response'    => [],
				'is_error'    => false,
				'status_code' => 200,
				'body'        => '<html><head><title>Test Page</title></head></html>',
			],
			'job_processor_send_api_result' => false,
			'manager_add_to_queue_result'   => false,
		],
		'expected' => [
			'job_processor_send_api_called' => true,
			'manager_add_to_queue_called'   => true,
			'result'                        => [
				'success' => false,
				'error'   => 'Failed to add page to monitoring queue.',
			],
		],
	],

	'testShouldDeleteAndReturnErrorWhenLimitExceeded' => [
		'config'   => [
			'input'                         => [
				'url' => 'https://example.com/page',
			],
			'environment_type'              => 'production',
			'context_is_allowed'            => true,
			'admin_url'                     => 'https://example.com/wp-admin/',
			'home_url'                      => 'https://example.com/',
			'http_response'                 => [
				'response'    => [],
				'is_error'    => false,
				'status_code' => 200,
				'body'        => '<html><head><title>Test Page</title></head></html>',
			],
			'job_processor_send_api_result' => [
				'uuid' => 'test-uuid-123',
			],
			'manager_add_to_queue_result'   => 5,
			'query_total_count'             => 11,
			'plan_max_urls'                 => 10,
		],
		'expected' => [
			'job_processor_send_api_called'          => true,
			'manager_add_to_queue_called'            => true,
			'manager_make_status_inprogress_called'  => true,
			'queue_schedule_called'                  => true,
			'query_delete_item_called'               => true,
			'result'                                 => [
				'success' => false,
				'error'   => 'URL limit exceeded.',
			],
		],
	],

	'testShouldSucceedForHomepage' => [
		'config'   => [
			'input'                         => [
				'url' => 'https://example.com/',
			],
			'environment_type'              => 'production',
			'context_is_allowed'            => true,
			'admin_url'                     => 'https://example.com/wp-admin/',
			'home_url'                      => 'https://example.com/',
			'http_response'                 => [
				'response'    => [],
				'is_error'    => false,
				'status_code' => 200,
				'body'        => '<html><head><title>My Site - Welcome</title></head></html>',
			],
			'job_processor_send_api_result' => [
				'uuid' => 'test-uuid-homepage',
			],
			'manager_add_to_queue_result'   => 1,
			'query_total_count'             => 5,
			'plan_max_urls'                 => 10,
			'plan_current_plan'             => 'pro',
		],
		'expected' => [
			'job_processor_send_api_called'         => true,
			'manager_add_to_queue_called'           => true,
			'manager_make_status_inprogress_called' => true,
			'queue_schedule_called'                 => true,
			'plan_get_current_plan_called'          => true,
			'action_fired'                          => true,
			'action_url'                            => 'https://example.com/',
			'action_plan'                           => 'pro',
			'action_urls_count'                     => 5,
			'result'                                => [
				'success' => true,
				'error'   => '',
			],
		],
	],

	'testShouldSucceedForRegularPage' => [
		'config'   => [
			'input'                         => [
				'url' => 'https://example.com/about-us',
			],
			'environment_type'              => 'production',
			'context_is_allowed'            => true,
			'admin_url'                     => 'https://example.com/wp-admin/',
			'home_url'                      => 'https://example.com/',
			'http_response'                 => [
				'response'    => [],
				'is_error'    => false,
				'status_code' => 200,
				'body'        => '<html><head><title>About Us | My Site</title></head></html>',
			],
			'job_processor_send_api_result' => [
				'uuid' => 'test-uuid-about',
			],
			'manager_add_to_queue_result'   => 2,
			'query_total_count'             => 3,
			'plan_max_urls'                 => 10,
			'plan_current_plan'             => 'free',
		],
		'expected' => [
			'job_processor_send_api_called'         => true,
			'manager_add_to_queue_called'           => true,
			'manager_make_status_inprogress_called' => true,
			'queue_schedule_called'                 => true,
			'plan_get_current_plan_called'          => true,
			'action_fired'                          => true,
			'action_url'                            => 'https://example.com/about-us',
			'action_plan'                           => 'free',
			'action_urls_count'                     => 3,
			'result'                                => [
				'success' => true,
				'error'   => '',
			],
		],
	],

	'testShouldSucceedWithAsyncFallbackWhenSyncFails' => [
		'config'   => [
			'input'                         => [
				'url' => 'https://example.com/contact',
			],
			'environment_type'              => 'production',
			'context_is_allowed'            => true,
			'admin_url'                     => 'https://example.com/wp-admin/',
			'home_url'                      => 'https://example.com/',
			'http_response'                 => [
				'response'    => [],
				'is_error'    => false,
				'status_code' => 200,
				'body'        => '<html><head><title>Contact Us</title></head></html>',
			],
			'job_processor_send_api_result' => false,
			'manager_add_to_queue_result'   => 3,
			'query_total_count'             => 4,
			'plan_max_urls'                 => 10,
			'plan_current_plan'             => 'free',
		],
		'expected' => [
			'job_processor_send_api_called' => true,
			'manager_add_to_queue_called'   => true,
			'plan_get_current_plan_called'  => true,
			'action_fired'                  => true,
			'action_url'                    => 'https://example.com/contact',
			'action_plan'                   => 'free',
			'action_urls_count'             => 4,
			'result'                        => [
				'success' => true,
				'error'   => '',
			],
		],
	],

	'testShouldAddProtocolToUrlWithoutProtocol' => [
		'config'   => [
			'input'                         => [
				'url' => 'example.com/page',
			],
			'environment_type'              => 'production',
			'context_is_allowed'            => true,
			'admin_url'                     => 'https://example.com/wp-admin/',
			'home_url'                      => 'https://example.com/',
			'http_response'                 => [
				'response'    => [],
				'is_error'    => false,
				'status_code' => 200,
				'body'        => '<html><head><title>Page Title</title></head></html>',
			],
			'job_processor_send_api_result' => [
				'uuid' => 'test-uuid-protocol',
			],
			'manager_add_to_queue_result'   => 4,
			'query_total_count'             => 5,
			'plan_max_urls'                 => 10,
			'plan_current_plan'             => 'free',
		],
		'expected' => [
			'job_processor_send_api_called'         => true,
			'manager_add_to_queue_called'           => true,
			'manager_make_status_inprogress_called' => true,
			'queue_schedule_called'                 => true,
			'plan_get_current_plan_called'          => true,
			'action_fired'                          => true,
			'action_url'                            => 'https://example.com/page',
			'action_plan'                           => 'free',
			'action_urls_count'                     => 5,
			'result'                                => [
				'success' => true,
				'error'   => '',
			],
		],
	],
];
