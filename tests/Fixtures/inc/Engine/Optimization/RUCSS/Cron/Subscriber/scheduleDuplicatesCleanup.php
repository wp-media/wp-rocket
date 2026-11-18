<?php

return [
	'test_data' => [
		'shouldScheduleWhenNotDoneAndNotScheduled' => [
			'config'   => [
				'done'      => false,
				'scheduled' => false,
			],
			'expected' => true,
		],
		'shouldNotScheduleWhenDone'                => [
			'config'   => [
				'done'      => true,
				'scheduled' => false,
			],
			'expected' => false,
		],
		'shouldNotScheduleWhenAlreadyScheduled'    => [
			'config'   => [
				'done'      => false,
				'scheduled' => 1700000000,
			],
			'expected' => false,
		],
	],
];
