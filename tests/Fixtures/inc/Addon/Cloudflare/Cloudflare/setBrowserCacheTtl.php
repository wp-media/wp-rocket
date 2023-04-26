<?php

return [
	'shouldReturnSecondsWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 30,
			'response' => (object) [
				'succcess' => true,
			],
			'request_error' => false,
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
			'request_error' => false,
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
			'request_error' => false,
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
			'request_error' => false,
		],
		'expected' => '2 days',
	],
	'shouldReturnWPErrorWhenError' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 30,
			'response' => new WP_Error( 'error' ),
			'request_error' => true,
		],
		'expected' => 'error',
	],
];
