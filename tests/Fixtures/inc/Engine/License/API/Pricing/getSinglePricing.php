<?php

return [
	'testShouldReturnNullWhenNotObject' => [
		'data'     => [
			'licenses' => [],
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenNotSet' => [
		'data'     => json_decode( json_encode( [
			'licenses' => [
				'plus' => [
					'prices'   => [],
					'websites' => 3,
				],
			],
		] ) ),
		'expected' => null,
	],
	'testShouldReturnObjectWhenSet' => [
		'data'     => json_decode( json_encode( [
			'licenses' => [
				'single' => [
					'prices'   => [],
					'websites' => 1,
				],
			],
		] ) ),
		'expected' => (object) [
			'prices'   => [],
			'websites' => 1,
		],
	],
];
