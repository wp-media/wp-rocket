<?php

return [
	'testShouldReturnZeroWhenNotObject' => [
		'data'     => [
			'promo' => [],
		],
		'expected' => 0,
	],
	'testShouldReturnZeroWhenPlusNotSet' => [
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
							'sale'    => 40,
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
					'prices' => [
						'from_single' => [
							'regular' => 50,
							'sale'    => 40,
						],
					],
				],
			],
		] ) ),
		'expected' => 50,
	],
];
