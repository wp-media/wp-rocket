<?php

return [
	'shouldCompleteWhenWriteSucceeds' => [
		'config' => [
			'optimization_type' => 'rucss',
			'write_result'      => true,
			'job_details'       => [
				'code'     => 200,
				'status'   => 'completed',
				'message'  => 'Treeshaked successfully!',
				'contents' => [
					'shakedCSS'      => 'body { color: red; }',
					'shakedCSS_size' => 58299,
				],
			],
			'row_details'       => [
				'url'       => 'http://example.com',
				'id'        => 100,
				'is_mobile' => 0,
				'status'    => 'in-progress',
			],
		],
		'expected' => [
			'make_status_completed' => true,
			'make_status_failed'    => false,
			'transient_set'         => false,
		],
	],
	'shouldFailAndSetTransientWhenWriteFails' => [
		'config' => [
			'optimization_type' => 'rucss',
			'write_result'      => false,
			'job_details'       => [
				'code'     => 200,
				'status'   => 'completed',
				'message'  => 'Treeshaked successfully!',
				'contents' => [
					'shakedCSS'      => 'body { color: red; }',
					'shakedCSS_size' => 58299,
				],
			],
			'row_details'       => [
				'url'       => 'http://example.com',
				'id'        => 100,
				'is_mobile' => 0,
				'status'    => 'in-progress',
			],
		],
		'expected' => [
			'make_status_completed' => false,
			'make_status_failed'    => true,
			'error_message'         => 'RUCSS: Could not write used CSS to the filesystem: http://example.com',
			'transient_set'         => true,
		],
	],
];
