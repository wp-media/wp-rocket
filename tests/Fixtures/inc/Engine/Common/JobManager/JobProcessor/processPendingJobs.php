<?php
return [
    'disabledShouldBailOut' => [
        'config' => [
			'enabled' => false,
			'rows_count' => 100,
        ],
		'expected' => [
			'rows_count' => 100,
			'in_progress' => null
		]
    ],
	'noPendingShouldBailOut' => [
		'configs' => [
			'rows_count' => 100,
			'enabled' => true,
			'rows' => [
			]
		],
		'expected' => [
			'rows_count' => 100,
			'in_progress' => null,
			'next_retry_time' => '2023-11-22 02:00:00',
		]
	],
	'pendingShouldPassInProgress' => [
		'configs' => [
			'rows_count' => 100,
			'enabled' => true,
			'rows' => [
				(object) [
					'id' => 10,
					'url' => 'http://example.org',
					'next_retry_time' => '2023-11-22 02:00:00',
					'is_mobile' => false,
				]
			]
		],
		'expected' => [
			'rows_count' => 100,
			'in_progress' => 10,
		]
	]
];
