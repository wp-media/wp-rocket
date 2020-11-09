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
				'infinite' => [
					'prices' => [
						'from_plus' => [
							'sale'    => 160,
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
					'prices' => [
						'from_plus' => [
							'regular' => 200,
							'sale'    => 160,
						],
					],
				],
			],
		] ) ),
		'expected' => 200,
	],
];
