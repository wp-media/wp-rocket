<?php

return [
	'testShouldDoNothingWhenNoCap' => [
		'config' => [
			'connection' => false,
			'cap' => false,
			'error' => false,
			'result' => '',
		],
		'expected' => null,
	],
	'testShouldDoExpectedWhenError' => [
		'config' => [
			'connection' => false,
			'cap' => true,
			'error' => true,
			'result' => new WP_Error( '401', 'error' ),
		],
		'expected' => 'expected',
	],
	'testShouldPurgeWhenSuccess' => [
		'config' => [
			'connection' => false,
			'cap' => true,
			'error' => false,
			'result' => ''
		],
		'expected' => 'expected',
	],
];
