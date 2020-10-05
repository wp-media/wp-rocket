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
				'infinite' => [
					'prices' => [
						'from_single' => [
							'regular' => 150,
						],
					],
				],
			],
		] ) ),
		'expected' => 150,
	],
	'testShouldReturnRegularIntWhenPropertySetAndNoPromo' => [
		'data'     => json_decode( json_encode( [
			'licenses' => [
				'infinite' => [
					'prices' => [
						'from_single' => [
							'regular' => 150,
							'sale'    => 120,
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
		'expected' => 150,
	],
	'testShouldReturnSaleIntWhenPropertySetAndPromo' => [
		'data'     => json_decode( json_encode( [
			'licenses' => [
				'infinite' => [
					'prices' => [
						'from_single' => [
							'regular' => 150,
							'sale'    => 120,
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
		'expected' => 120,
	],
];
