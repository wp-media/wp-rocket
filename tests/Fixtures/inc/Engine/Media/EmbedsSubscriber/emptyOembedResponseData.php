<?php

return [
	'test_data' => [
		'testShouldDoNothingWhenBypass' => [
			'config' => [
				'options' => [
					'embeds' => 1,
				],
				'bypass' => true,
				'rest_request' => true,
			],
			'data'     => [
				'test' => 'test',
			],
			'expected' => true,
		],
		'testShouldDoNothingWhenOptionDisabled' => [
			'config' => [
				'options' => [
					'embeds' => 0,
				],
				'bypass' => false,
				'rest_request' => true,
			],
			'data'     => [
				'test' => 'test',
			],
			'expected' => true,
		],
		'testShouldDoNothingWhenNotRESTRequest' => [
			'config' => [
				'options' => [
					'embeds' => 1,
				],
				'bypass' => false,
				'rest_request' => false,
			],
			'data'     => [
				'test' => 'test',
			],
			'expected' => true,
		],
		'testShouldReturnEmptyArray' => [
			'config' => [
				'options' => [
					'embeds' => 1,
				],
				'bypass' => false,
				'rest_request' => true,
			],
			'data'     => [
				'test' => 'test',
			],
			'expected' => false,
		],
	],
];
