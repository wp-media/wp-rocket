<?php

return [
	'expectStatusCompleted' => [
		'config'   => [
			'job_id'                => 1,
			'row_details'           => [
				'job_id'     => 1,
				'url'        => 'http://example.com',
				'queue_name' => 1,
				'is_home'    => 1,
				'is_mobile'  => 0,
				'retries'    => 0,
			],
			'job_details'           => [
				'code'     => 200,
				'contents' => [
					'shakedCSS' => '.test{color:red}'
				],
				'is_home'  => 1,
			],
			'is_used_css_file_written'       => 1
		],
		'expected' => '',

	],
	'expectStatusFailedNoRetries' => [
		'config'   => [
			'job_id'                => 1,
			'row_details'           => [
				'job_id'     => 1,
				'url'        => 'http://example.com',
				'queue_name' => 1,
				'is_home'    => 1,
				'is_mobile'  => 0,
				'retries'    => 4,
			],
			'job_details'           => [
				'code'     => 500,
				'message' => 'ERROR'
			],
		],
		'expected' => '',

	],
	'expectStatusFailedWithRetries' => [
		'config'   => [
			'job_id'                => 1,
			'row_details'           => [
				'job_id'     => 1,
				'url'        => 'http://example.com',
				'queue_name' => 1,
				'is_home'    => 1,
				'is_mobile'  => 0,
				'retries'    => 2,
			],
			'job_details'           => [
				'code'     => 500,
				'message' => 'ERROR'
			],
		],
		'expected' => '',

	],
	'expectStatusTimeoutWithRetries' => [
		'config'   => [
			'job_id'                => 1,
			'row_details'           => [
				'job_id'     => 1,
				'url'        => 'http://example.com',
				'queue_name' => 1,
				'is_home'    => 1,
				'is_mobile'  => 0,
				'retries'    => 1,
			],
			'job_details'           => [
				'code'     => 408,
				'message' => 'timeout'
			],
			'add_to_queue_response' => [
				'code'     => 200,
				'contents' => [ 'jobId' => 2 ]
			],
		],
		'expected' => '',

	],
];
