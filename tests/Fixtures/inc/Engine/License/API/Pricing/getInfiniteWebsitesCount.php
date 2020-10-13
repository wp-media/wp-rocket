<?php

return [
	'testShouldReturnZeroWhenNotObject' => [
		'data'     => [
			'promo' => [],
		],
		'expected' => 0,
	],
	'testShouldReturnZeroWhenSingleNotSet' => [
		'data'     => json_decode( json_encode( [
			'licenses' => [],
		] ) ),
		'expected' => 0,
	],
	'testShouldReturnZeroWhenPropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'licenses' => [
				'infinite' => [
					'prices' => [
						'from_single' => [
							'regular' => 150,
						],
					],
				],
			],
		] ) ),
		'expected' => 0,
	],
	'testShouldReturnIntWhenPropertySet' => [
		'data'     => json_decode( json_encode( [
			'licenses' => [
				'infinite' => [
					'websites' => -1,
				],
			],
		] ) ),
		'expected' => -1,
	],
];
