<?php
return [
	'test_data' => [

		'ShouldChangeOptionsWhenUpdateFromVersionBefore3.8' => [
			'config'   => [
				'old_version' => '3.7',
				'options' => [
					'purge_cron_interval' => 10,
					'purge_cron_unit' => 'MINUTE_IN_SECONDS'
				]
			],
			'expected' => [
				'purge_cron_interval' => 1,
				'purge_cron_unit' => 'HOUR_IN_SECONDS'
			]
		],

		'ShouldChangeOptionsWhenUpdateFromVersion3.8' => [
			'config'   => [
				'old_version' => '3.8',
				'options' => [
					'purge_cron_interval' => 10,
					'purge_cron_unit' => 'MINUTE_IN_SECONDS'
				]
			],
			'expected' => [
				'purge_cron_interval' => 1,
				'purge_cron_unit' => 'HOUR_IN_SECONDS'
			]
		],

		'ShouldNotChangeOptionsWhenUpdateFromVersionAbove3.8' => [
			'config'   => [
				'old_version' => '3.9',
				'options' => [
					'purge_cron_interval' => 10,
					'purge_cron_unit' => 'HOUR_IN_SECONDS'
				]
			],
			'expected' => [
				'bailout' => true,
				'purge_cron_interval' => 1,
				'purge_cron_unit' => 'HOUR_IN_SECONDS'
			]
		],

	]
];
