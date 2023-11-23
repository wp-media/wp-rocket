<?php

return [
	'ApiGives408' => [
		'config' => [
			'job_id' => 1,
			'job_details' => [
				'id'             => '1',
				'code'			 => 408,
				'message'		 => 'Error',
			],
			'row_details' => (object) [
				'id' 			 => 1,
				'job_id'		 => '',
				'status' 		 => 'in-progress',
				'url'			 => 'https://example.com/page',
				'is_mobile'		 => false,
				'queue_name'	 => ''
			],
		],
		'expected' => [],
	],
];
