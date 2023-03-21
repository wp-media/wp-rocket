<?php

return [
	'shouldReturnSecondsWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 30,
			'response' => (object) [
				'succcess' => true,
			],
		],
		'expected' => '30 seconds',
	],
	'shouldReturnMinutesWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 120,
			'response' => (object) [
				'succcess' => true,
			],
		],
		'expected' => '2 minutes',
	],
	'shouldReturnHoursWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 7200,
			'response' => (object) [
				'succcess' => true,
			],
		],
		'expected' => '2 hours',
	],
	'shouldReturnDaysWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 172800,
			'response' => (object) [
				'succcess' => true,
			],
		],
		'expected' => '2 days',
	],
	'shouldReturnWPErrorWhenException' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 30,
			'response' => 'exception',
		],
		'expected' => 'error',
	],
];
