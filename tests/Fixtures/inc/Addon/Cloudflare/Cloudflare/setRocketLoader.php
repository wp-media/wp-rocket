<?php

return [
	'shouldReturnValueWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 'on',
			'response' => (object) [
				'succcess' => true,
			],
		],
		'expected' => 'on',
	],
	'shouldReturnWPErrorWhenException' => [
		'config' => [
			'zone_id' => '12345',
			'value' => 'off',
			'response' => 'exception',
		],
		'expected' => 'error',
	],
];
