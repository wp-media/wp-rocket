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
];
