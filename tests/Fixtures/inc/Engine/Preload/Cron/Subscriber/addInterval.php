<?php

return [
	'testShouldReturnSameNothingOnDisable' => [
		'config' => [
			'schedules' => [
				'testkey' => [
					'interval' => 10,
					'display'  => 'test',
				]
			],
			'is_enabled' => false,
		],
		'expected' => [
			'testkey' => [
				'interval' => 10,
				'display'  => 'test',
			],
		]
	],
	'testShouldReturnNewSchedulesNothingOnEnabled' => [
		'config' => [
			'schedules' => [
				'testkey' => [
					'interval' => 10,
					'display'  => 'test',
				],
			],
			'is_enabled' => true,
		],
		'expected' => [
			'testkey' => [
				'interval' => 10,
				'display'  => 'test',
			],
			'rocket_preload_process_pending' => [
				'interval' => 60,
				'display'  => 'WP Rocket Preload pending jobs',
			]
		]
	]
];
