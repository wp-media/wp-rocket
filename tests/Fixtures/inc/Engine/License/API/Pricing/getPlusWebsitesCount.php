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
				'plus' => [
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
				'plus' => [
					'websites' => 3,
				],
			],
		] ) ),
		'expected' => 3,
	],
];
