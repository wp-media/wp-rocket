<?php

$time_table = [
	1 => 180,  // 3 minutes
	2 => 300,  // 5 minutes
	3 => 600,  // 10 minutes
	4 => 900,  // 15 minutes.
	5 => 1200, // 20 minutes.
	6 => 1800, // 30 minutes.
];

return [
	'ShouldRetryMore' => [
		'config' => [
			'job_id' => 1,
			'job_details' => [
				'id'             => '1',
				'code'			 => 400,
				'message'		 => 'Error',
			],
			'row_details' => (object) [
				'id' 			 => 1,
				'job_id' => 1,
				'status' 		 => 'in-progress',
				'retries'		=> 0,
				'url'	=> 'https://example.org/page',
				'error_message' => '',
			],
			'time_table' => $time_table,
			'duration_retry' => 180
		],
		'expected' => [
			'row_details' => [
				'id' => 1,
				'status' => 'pending',
				'retries' => 1,
				'not_process_before' => 180,
			]
		]
	],
	'ShouldFail' => [
		'config' => [
			'job_id' => 2,
			'job_details' => [
				'id' 			 => '2',
				'code' 		 	 => 400,
				'message'		 => 'Error',
			],
			'row_details' => (object) [
				'id' 			 => 2,
				'job_id' => 2,
				'status' 		 => 'in-progress',
				'url'	=> 'https://example.org/page',
				'error_message' => '',
				'retries' => 10
			],
			'duration_retry' => 180,
			'time_table' => $time_table,
		],
		'expected' => [
			'row_details' => [
				'id' 			 => 2,
				'status' 		 => 'failed',
			],

		]
	],
];
