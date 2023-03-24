<?php

return [
	'testShouldDoNothingWhenNoCap' => [
		'config' => [
			'cap' => false,
			'error' => false,
			'result' => '',
		],
		'expected' => null,
	],
	'testShouldDoExpectedWhenError' => [
		'config' => [
			'cap' => true,
			'error' => true,
			'result' => new WP_Error( '401', 'error' ),
		],
		'expected' => 'expected',
	],
	'testShouldPurgeWhenSuccess' => [
		'config' => [
			'cap' => true,
			'error' => false,
			'result' => ''
		],
		'expected' => 'expected',
	],
];
