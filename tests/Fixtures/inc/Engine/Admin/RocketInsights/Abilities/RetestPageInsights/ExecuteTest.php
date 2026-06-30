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
				'status'  => 'failed',
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
				'status'  => 'failed',
				'error'   => 'Performance monitoring is disabled.',
			],
		],
	],

	'testShouldReturnNotFoundWhenUrlNotTracked' => [
		'config'   => [
			'input'                  => [
				'url' => 'https://example.com/not-tracked',
			],
			'environment_type'       => 'production',
			'context_is_allowed'     => true,
			'manager_get_single_job' => false,
		],
		'expected' => [
			'result' => [
				'success' => false,
				'status'  => 'not_found',
				'error'   => 'URL is not tracked by Rocket Insights.',
			],
		],
	],

	'testShouldReturnRunningWhenTestIsToSubmit' => [
		'config'   => [
			'input'              => [
				'url' => 'https://example.com/page',
			],
			'environment_type'   => 'production',
			'context_is_allowed' => true,
			'row_is_running'     => true,
			'row_id'             => 10,
		],
		'expected' => [
			'result' => [
				'success' => true,
				'status'  => 'running',
				'error'   => '',
			],
		],
	],

	'testShouldReturnRunningWhenTestIsPending' => [
		'config'   => [
			'input'              => [
				'url' => 'https://example.com/page',
			],
			'environment_type'   => 'production',
			'context_is_allowed' => true,
			'row_is_running'     => true,
			'row_id'             => 11,
		],
		'expected' => [
			'result' => [
				'success' => true,
				'status'  => 'running',
				'error'   => '',
			],
		],
	],

	'testShouldReturnRunningWhenTestIsInProgress' => [
		'config'   => [
			'input'              => [
				'url' => 'https://example.com/page',
			],
			'environment_type'   => 'production',
			'context_is_allowed' => true,
			'row_is_running'     => true,
			'row_id'             => 12,
		],
		'expected' => [
			'result' => [
				'success' => true,
				'status'  => 'running',
				'error'   => '',
			],
		],
	],

	'testShouldReturnFailedWhenSubmissionFails' => [
		'config'   => [
			'input'                         => [
				'url' => 'https://example.com/page',
			],
			'environment_type'              => 'production',
			'context_is_allowed'            => true,
			'row_is_running'                => false,
			'row_id'                        => 42,
			'job_processor_send_api_result' => false,
			'manager_add_to_queue_result'   => false,
		],
		'expected' => [
			'job_processor_send_api_called' => true,
			'manager_add_to_queue_called'   => true,
			'assert_additional_details'     => true,
			'result'                        => [
				'success' => false,
				'status'  => 'failed',
				'error'   => 'Unable to reset performance test. Please try again.',
			],
		],
	],

	'testShouldRetestSuccessfullyWithSyncSubmission' => [
		'config'   => [
			'input'                         => [
				'url' => 'https://example.com/page',
			],
			'environment_type'              => 'production',
			'context_is_allowed'            => true,
			'row_is_running'                => false,
			'row_id'                        => 42,
			'job_processor_send_api_result' => [
				'uuid' => 'test-uuid-retest',
			],
			'manager_add_to_queue_result'   => 42,
		],
		'expected' => [
			'job_processor_send_api_called'         => true,
			'manager_add_to_queue_called'           => true,
			'assert_additional_details'             => true,
			'manager_make_status_inprogress_called' => true,
			'queue_schedule_called'                 => true,
			'action_fired'                          => true,
			'action_row_id'                         => 42,
			'result'                                => [
				'success' => true,
				'status'  => 'triggered',
				'error'   => '',
			],
		],
	],

	'testShouldRetestSuccessfullyWithAsyncFallback' => [
		'config'   => [
			'input'                         => [
				'url' => 'https://example.com/page',
			],
			'environment_type'              => 'production',
			'context_is_allowed'            => true,
			'row_is_running'                => false,
			'row_id'                        => 99,
			'job_processor_send_api_result' => false,
			'manager_add_to_queue_result'   => 99,
		],
		'expected' => [
			'job_processor_send_api_called' => true,
			'manager_add_to_queue_called'   => true,
			'assert_additional_details'     => true,
			'action_fired'                  => true,
			'action_row_id'                 => 99,
			'result'                        => [
				'success' => true,
				'status'  => 'triggered',
				'error'   => '',
			],
		],
	],
];
