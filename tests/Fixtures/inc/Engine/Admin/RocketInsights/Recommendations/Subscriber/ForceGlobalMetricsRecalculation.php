<?php

return [
	'test_data' => [
		'shouldDeleteTransientWhenUpgradingFromVersionBelow3_21' => [
			'config'   => [
				'new_version'     => '3.21',
				'old_version'     => '3.20',
				'transient_value' => [
					'score'      => 85,
					'pages_num'  => 5,
					'status'     => 'complete',
					'is_running' => false,
				],
			],
			'expected' => [
				'transient_before' => [
					'score'      => 85,
					'pages_num'  => 5,
					'status'     => 'complete',
					'is_running' => false,
				],
				'transient_after'  => false,
			],
		],

		'shouldNotDeleteTransientWhenUpgradingFromVersion3_21OrHigher' => [
			'config'   => [
				'new_version'     => '3.22',
				'old_version'     => '3.21',
				'transient_value' => [
					'score'      => 90,
					'pages_num'  => 10,
					'status'     => 'complete',
					'is_running' => false,
				],
			],
			'expected' => [
				'transient_before' => [
					'score'      => 90,
					'pages_num'  => 10,
					'status'     => 'complete',
					'is_running' => false,
				],
				'transient_after'  => [
					'score'      => 90,
					'pages_num'  => 10,
					'status'     => 'complete',
					'is_running' => false,
				],
			],
		],

		'shouldDoNothingWhenNoTransientExistsAndUpgradingFromOldVersion' => [
			'config'   => [
				'new_version'     => '3.21',
				'old_version'     => '3.19',
				'transient_value' => null,
			],
			'expected' => [
				'transient_before' => false,
				'transient_after'  => false,
			],
		],
	],
];
