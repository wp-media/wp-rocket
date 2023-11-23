<?php

return [
	'ApiGives404' => [
		'config' => [
			'job_id' => 1,
			'job_details' => [
				'id'             => '1',
				'code'			 => 404,
				'message'		 => 'Error',
			],
			'row_details' => [
				'id' 			 => 1,
				'status' 		 => 'in-progress',
			],
		],
		'expected' => [],
	],
	'ApiGives422' => [
		'config' => [
			'job_id' => 2,
			'job_details' => [
				'id' 			 => '2',
				'code' 		 	 => 422,
				'message'		 => 'Error',
			],
			'row_details' => [
				'id' 			 => 2,
				'status' 		 => 'in-progress',
			],
		],
		'expected' => []
	],
	'ApiGives500' => [
		'config' => [
			'job_id' => 3,
			'job_details' => [
				'id' 			 => '3',
				'code' 		 	 => 500,
				'message' 		 => 'Error'
			],
			'row_details' => [
				'id'			 => 3,
				'status' 		 => 'in-progress',
			]
		],
		'expected' => [],
	],
];
