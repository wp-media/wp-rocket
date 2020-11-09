<?php

return [
	'testShouldReturnNullWhenNoCap' => [
		'config' => [
			'cap'       => false,
			'transient' => false,
		],
		'expected' => false,
	],
	'testShouldReturnNullWhenTransientExists' => [
		'config' => [
			'cap'       => true,
			'transient' => 1,
		],
		'expected' => false,
	],
	'testShouldSetTransientWhenTransientNotExists' => [
		'config' => [
			'cap'       => true,
			'transient' => false,
		],
		'expected' => true,
	],
];
