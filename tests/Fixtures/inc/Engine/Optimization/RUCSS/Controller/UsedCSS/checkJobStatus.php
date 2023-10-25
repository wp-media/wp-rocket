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
					'shakedCSS' => '.test{color:red}.test_1{color:blue}.test_2{color:black}.test_3{color:red}.test_4{color:blue}.test_5{color:black}.test_6{color:black}.test_7{color:red}.test_8{color:blue}.test_9{color:black}',
					'shakedCSS_size' => '150'
				],
				'is_home'  => 1,
			],
			'is_used_css_file_written'       => 1
		],
		'expected' => '',

	],
	'expectStatusFaildMinCSS_Size' => [
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
					'shakedCSS' => '.test{color:red}',
					'shakedCSS_size' => '16'
				],
				'is_home'  => 1,
			],
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
		],
		'expected' => '',

	],
];
