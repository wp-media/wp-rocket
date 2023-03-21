<?php

return [
	'shouldReturnValueWhenRequestSuccessful' => [
		'config' => [
			'zone_id' => '12345',
			'value'   => 'aggressive',
			'response' => (object) [
				'succcess' => true,
			],
		],
		'expected' => 'aggressive',
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
