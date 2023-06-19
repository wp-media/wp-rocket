<?php

return [
	'testShouldReturnDefaultWhenOptionDisabled' => [
		'config' => [
			'filter' => false,
			'option' => false,
			'value' => 'localhost',
		],
		'expected' => 'localhost',
	],
	'testShouldReturnUpdatedWhenOptionEnabled' => [
		'config' => [
			'filter' => false,
			'option' => true,
			'value' => 'localhost',
		],
		'expected' => 'example.org',
	],
	'testShouldReturnUpdatedWhenFilterEnabled' => [
		'config' => [
			'filter' => true,
			'option' => false,
			'value' => 'localhost',
		],
		'expected' => 'example.org',
	],
];
