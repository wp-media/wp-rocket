<?php

return [
	'shouldReturnTrueWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'response' => (object) [
				'succcess' => true,
			],
			'urls' => [
				'about',
				'contact',
			],
		],
		'expected' => true,
	],
	'shouldReturnWPErrorWhenException' => [
		'config' => [
			'zone_id' => '12345',
			'response' => 'exception',
			'urls' => [
				'about',
				'contact',
			],
		],
		'expected' => 'error',
	],
];
