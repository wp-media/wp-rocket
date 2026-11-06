<?php
return [
    'resetShouldUpdateItem' => [
        'config' => [
              'id' => 0,
              'job_id' => 'EU-1',
			  'now' => '2023-10-11 20:21:00',
			  'updated' => true,
        ],
        'expected' => [
			'id' => 0,
			'data' => [
				'job_id'        => 'EU-1',
				'status'        => 'to-submit',
				'error_code'    => '',
				'error_message' => '',
				'retries'       => 0,
				'modified'      => '2023-10-11 20:21:00',
				'submitted_at'  => '2023-10-11 20:21:00',
			],
			'result' => true
        ]
    ],
	'resetJobIdMissingShouldUpdateItem' => [
		'config' => [
			'id' => 0,
			'job_id' => '',
			'now' => '2023-10-11 20:21:00',
			'updated' => true,
		],
		'expected' => [
			'id' => 0,
			'data' => [
				'job_id'        => '',
				'status'        => 'to-submit',
				'error_code'    => '',
				'error_message' => '',
				'retries'       => 0,
				'modified'      => '2023-10-11 20:21:00',
				'submitted_at'  => '2023-10-11 20:21:00',
			],
			'result' => true
		]
	],
	'resetWithAdditionalDetailsShouldOverrideDefaults' => [
		'config' => [
			'id' => 0,
			'job_id' => 'EU-2',
			'now' => '2023-10-11 20:21:00',
			'updated' => true,
			'additional_details' => [
				'data' => '{"source":"re-test post type listing","is_retest":true}',
				'status' => 'pending',
			],
		],
		'expected' => [
			'id' => 0,
			'data' => [
				'job_id'        => 'EU-2',
				'status'        => 'pending',
				'error_code'    => '',
				'error_message' => '',
				'retries'       => 0,
				'modified'      => '2023-10-11 20:21:00',
				'submitted_at'  => '2023-10-11 20:21:00',
				'data'          => '{"source":"re-test post type listing","is_retest":true}',
			],
			'result' => true
		]
	],
];
