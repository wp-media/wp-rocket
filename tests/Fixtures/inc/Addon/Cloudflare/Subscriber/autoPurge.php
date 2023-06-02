<?php

return [
	'testShouldDoNothingWhenNoCap' => [
		'config' => [
			'cap' => false,
			'error' => false,
			'page_rule' => true,
		],
		'expected' => null,
	],
	'testShouldDoNothingWhenNoRule' => [
		'config' => [
			'cap' => true,
			'error' => false,
			'page_rule' => false,
		],
		'expected' => null,
	],
	'testShouldDoNothingWhenError' => [
		'config' => [
			'cap' => true,
			'error' => true,
			'page_rule' => true,
		],
		'expected' => null,
	],
	'testShouldPurgeWhenHasRule' => [
		'config' => [
			'cap' => true,
			'error' => false,
			'page_rule' => true,
		],
		'expected' => 'expected',
	],
];
