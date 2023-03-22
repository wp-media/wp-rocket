<?php

return [
	'testShouldReturnNullWhenNoCap' => [
		'config' => [
			'cap' => false,
			'transient' => [
				'result' => '',
				'message' => '',
			],
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenNoTransient' => [
		'config' => [
			'cap' => true,
			'transient' => false,
		],
		'expected' => null,
	],
	'testShouldReturnNoticeWhenTransient' => [
		'config' => [
			'cap' => true,
			'transient' => [
				'result' => '',
				'message' => '',
			],
		],
		'expected' => [
			'result' => '',
			'message' => '',
		],
	],
];
