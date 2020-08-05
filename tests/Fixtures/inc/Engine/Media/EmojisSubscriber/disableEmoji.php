<?php

return [
	'test_data' => [
		'testShouldDoNothingWhenBypass' => [
			'config' => [
				'options' => [
					'emoji' => 1,
				],
				'bypass' => true,
			],
			'expected' => false,
		],
		'testShouldDoNothingWhenOptionDisabled' => [
			'config' => [
				'options' => [
					'emoji' => 0,
				],
				'bypass' => false,
			],
			'expected' => false,
		],
		'testShouldUpdateActions&Filters' => [
			'config' => [
				'options' => [
					'emoji' => 1,
				],
				'bypass' => false,
			],
			'expected' => true,
		],
	],
];
