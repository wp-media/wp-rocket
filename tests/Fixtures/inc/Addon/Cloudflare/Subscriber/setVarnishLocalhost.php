<?php

return [
	'testShouldReturnDefaultWhenOptionDisabled' => [
		'config' => [
			'filter' => false,
			'option' => false,
			'value' => [],
		],
		'expected' => [],
	],
	'testShouldReturnUpdatedWhenOptionEnabled' => [
		'config' => [
			'filter' => false,
			'option' => true,
			'value' => [],
		],
		'expected' => [
			'localhost',
		],
	],
	'testShouldReturnUpdatedWhenFilterEnabled' => [
		'config' => [
			'filter' => true,
			'option' => false,
			'value' => [],
		],
		'expected' => [
			'localhost',
		],
	],
	'testShouldReturnUpdatedWhenValueIsString' => [
		'config' => [
			'filter' => true,
			'option' => false,
			'value' => '192.168.0.1',
		],
		'expected' => [
			'192.168.0.1',
			'localhost',
		],
	],
];
