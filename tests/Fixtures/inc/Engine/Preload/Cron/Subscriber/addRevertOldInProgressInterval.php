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
			'rocket_revert_old_in_progress_rows' => [
				'interval' => 12 * 60 * 60,
				'display'  => 'WP Rocket Preload revert stuck in-progress jobs',
			]
		]
	]
];
