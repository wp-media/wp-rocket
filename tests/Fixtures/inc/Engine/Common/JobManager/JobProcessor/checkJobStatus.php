<?php

return [
	'expectStatusCompletedRUCSS' => [
		'config'   => [
			'row_details'           => [
				'job_id'     => 1,
				'url'        => 'http://example.com',
				'queue_name' => 1,
				'is_home'    => 1,
				'is_mobile'  => 0,
				'retries'    => 0,
				'css'		 => '',
			],
			'job_details'           => [
				'code'     => 200,
				'contents' => [],
				'is_home'  => 1,
			],
			'optimization_type' => 'rucss',
		],
		'expected' => '',
	],
	'expectStatusCompletedATF' => [
		'config'   => [
			'row_details'           => [
				'job_id'     => 1,
				'url'        => 'http://example.com',
				'queue_name' => 1,
				'is_home'    => 1,
				'is_mobile'  => 0,
				'retries'    => 0,
				'lcp'		 => '',
			],
			'job_details'           => [
				'code'     => 200,
				'contents' => [],
				'is_home'  => 1,
			],
			'optimization_type' => 'atf',
		],
		'expected' => '',
	],
	'expectStatusFailedNoRetries' => [
		'config'   => [
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
			'optimization_type' => 'all',
		],
		'expected' => '',
	],
	'expectStatusFailedWithRetries' => [
		'config'   => [
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
			'optimization_type' => 'all',
		],
		'expected' => '',
	],
	'expectStatusTimeoutWithRetries' => [
		'config'   => [
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
			'optimization_type' => 'all',
		],
		'expected' => '',
	],
];
