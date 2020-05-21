<?php


return [
	'testShouldDoNothingWhenStatusEmpty' => [
		'status' => '',
		'expected' => [
			'rocketcdn_process' => true,
		]
	],

	'testShouldDeleteOptionWhenStatusFalse' => [
		'status' => 'false',
		'expected' => [
			'rocketcdn_process' => false,
		]
	],

	'testShouldUpdateOptionWhenStatusTrue' => [
		'status' => 'true',
		'expected' => [
			'rocketcdn_process' => true,
		]
	],
];
