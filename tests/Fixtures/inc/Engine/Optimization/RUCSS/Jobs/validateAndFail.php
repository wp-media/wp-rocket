<?php
return [
	'shouldReturnErrorMessage' => [
		'config' => [
			'optimization_type' => 'rucss',
			'min_size' => 1000000000,
			'job_details' => [
				'code' => 200,
				'status' => 'completed',
				'message' => 'Treeshaked successfully!',
				'contents' => [
					'shakedCSS_size' => 58299,
				],
			],
			'row_details' => [
				'url' => 'http://example.com',
				'id'  => 100,
				'is_mobile' => 1,
				'status' => 'in-progress'
			]
		],
		'expected' => []
	],
	'shouldNotReturnError' => [
		'config' => [
			'optimization_type' => 'all',
			'min_size' => 150,
			'job_details' => [
				'code' => 200,
				'status' => 'completed',
				'message' => 'Treeshaked successfully!',
				'contents' => [
					'shakedCSS_size' => 58299,
				],
			],
			'row_details' => [
				'url' => 'http://example.com',
				'id'  => 100,
				'is_mobile' => 1,
				'status' => 'in-progress'
			]
		],
		'expected' => []
	]
];
