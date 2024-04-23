<?php
return [
    'createShouldAdd' => [
        'config' => [
              'url' => 'https://example.org',
              'job_id' => 'EU-1',
              'queue_name' => 'EU',
              'is_mobile' => false,
			  'now' => '2023-10-11 20:21:00',
			  'result' => true,
		],
        'expected' => [
			'item' => [
				'url' => 'https://example.org',
				'job_id' => 'EU-1',
				'queue_name' => 'EU',
				'is_mobile' => false,
				'status'        => 'to-submit',
				'retries'       => 0,
				'last_accessed' => '2023-10-11 20:21:00',
			],
			'result' => true
        ]
    ],
	'createWithoutJobIdAndQueueNameShouldAdd' => [
		'config' => [
			'url' => 'https://example.org',
			'job_id' => '',
			'queue_name' => '',
			'is_mobile' => false,
			'now' => '2023-10-11 20:21:00',
			'result' => true,
		],
		'expected' => [
			'item' => [
				'url' => 'https://example.org',
				'job_id' => '',
				'queue_name' => '',
				'is_mobile' => false,
				'status'        => 'to-submit',
				'retries'       => 0,
				'last_accessed' => '2023-10-11 20:21:00',
			],
			'result' => true
		]
	]

];
