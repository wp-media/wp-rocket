<?php

return [
	'test_data' => [
		'testShouldDoNothingWhenBypass' => [
			'config' => [
				'options' => [
					'embeds' => 1,
				],
				'bypass' => true,
			],
			'expected' => false,
		],
		'testShouldDoNothingWhenOptionDisabled' => [
			'config' => [
				'options' => [
					'embeds' => 0,
				],
				'bypass' => false,
			],
			'expected' => false,
		],
		'testShouldUpdateActions&Filters' => [
			'config' => [
				'options' => [
					'embeds' => 1,
				],
				'bypass' => false,
			],
			'expected' => true,
		],
	],
];
