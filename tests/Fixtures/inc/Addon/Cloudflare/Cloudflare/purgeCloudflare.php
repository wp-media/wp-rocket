<?php

return [
	'shouldReturnTrueWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'succcess' => true,
			],
		],
		'expected' => true,
	],
	'shouldReturnWPErrorWhenException' => [
		'config' => [
			'zone_id' => '12345',
			'response' => 'exception',
		],
		'expected' => 'error',
	],
];
