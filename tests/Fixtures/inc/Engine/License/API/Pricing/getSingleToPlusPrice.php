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
	'testShouldReturnRegularWhenSalePropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'licenses' => [
				'plus' => [
					'prices' => [
						'from_single' => [
							'regular' => 50,
						],
					],
				],
			],
		] ) ),
		'expected' => 50,
	],
	'testShouldReturnRegularIntWhenPropertySetAndNoPromo' => [
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
			'promo' => [
				'name'             => 'Halloween',
				'discount_percent' => 20,
				'start_date'       => strtotime( 'tomorrow' ),
				'end_date'         => strtotime( 'next month' ),
			],
		] ) ),
		'expected' => 50,
	],
	'testShouldReturnSaleIntWhenPropertySetAndPromo' => [
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
			'promo' => [
				'name'             => 'Halloween',
				'discount_percent' => 20,
				'start_date'       => strtotime( 'last week' ),
				'end_date'         => strtotime( 'next week' ),
			],
		] ) ),
		'expected' => 40,
	],
];
