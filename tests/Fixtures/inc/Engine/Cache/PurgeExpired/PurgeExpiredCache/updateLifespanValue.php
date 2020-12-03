<?php
return [
	'test_data' => [

		'ZeroInMinutesLifespan' => [
			'config'   => [
				'options' => [
					'purge_cron_interval' => 0,
					'purge_cron_unit' => 'MINUTE_IN_SECONDS'
				]
			],
			'expected' => [
				'purge_cron_interval' => 0,
				'purge_cron_unit' => 'HOUR_IN_SECONDS'
			]
		],

		'UnderHalfHourInMinutesLifespan' => [
			'config'   => [
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

		'UnderOneHourInMinutesLifespan' => [
			'config'   => [
				'options' => [
					'purge_cron_interval' => 59,
					'purge_cron_unit' => 'MINUTE_IN_SECONDS'
				]
			],
			'expected' => [
				'purge_cron_interval' => 1,
				'purge_cron_unit' => 'HOUR_IN_SECONDS'
			]
		],

		'AboveOneHourInMinutesDirectlyLifespan' => [
			'config'   => [
				'options' => [
					'purge_cron_interval' => 70,
					'purge_cron_unit' => 'MINUTE_IN_SECONDS'
				]
			],
			'expected' => [
				'purge_cron_interval' => 1,
				'purge_cron_unit' => 'HOUR_IN_SECONDS'
			]
		],

		'UnderTwoHoursInMinutesDirectlyLifespan' => [
			'config'   => [
				'options' => [
					'purge_cron_interval' => 110,
					'purge_cron_unit' => 'MINUTE_IN_SECONDS'
				]
			],
			'expected' => [
				'purge_cron_interval' => 2,
				'purge_cron_unit' => 'HOUR_IN_SECONDS'
			]
		],

		'FiveHoursInMinutesLifespan' => [
			'config'   => [
				'options' => [
					'purge_cron_interval' => 300,
					'purge_cron_unit' => 'MINUTE_IN_SECONDS'
				]
			],
			'expected' => [
				'purge_cron_interval' => 5,
				'purge_cron_unit' => 'HOUR_IN_SECONDS'
			]
		],

		'BailoutWithHourUnit' => [
			'config'   => [
				'options' => [
					'purge_cron_interval' => 3,
					'purge_cron_unit' => 'HOUR_IN_SECONDS'
				]
			],
			'expected' => [
				'purge_cron_interval' => 3,
				'purge_cron_unit' => 'HOUR_IN_SECONDS'
			]
		],

		'BailoutWithDayUnit' => [
			'config'   => [
				'options' => [
					'purge_cron_interval' => 3,
					'purge_cron_unit' => 'DAY_IN_SECONDS'
				]
			],
			'expected' => [
				'purge_cron_interval' => 3,
				'purge_cron_unit' => 'DAY_IN_SECONDS'
			]
		],

	]
];
