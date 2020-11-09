<?php

return [
	'testShouldReturnFalseWhenNotObject' => [
		'data'     => [
			'promo' => [],
		],
		'expected' => false,
	],
	'testShouldReturnFalseWhenPromoNotSet' => [
		'data'     => json_decode( json_encode( [
			'licenses' => [],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenPromoPropertiesNotSet' => [
		'data'     => json_decode( json_encode( [
			'promo' => [
				'name'             => 'Halloween',
				'discount_percent' => 20,
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenPromoStartDatePropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'promo' => [
				'name'             => 'Halloween',
				'discount_percent' => 20,
				'end_date'         => 1604361600,
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenPromoEndDatePropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'promo' => [
				'name'             => 'Halloween',
				'discount_percent' => 20,
				'start_date'       => 1603756800,
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenPromoNotActive' => [
		'data'     => json_decode( json_encode( [
			'promo' => [
				'name'             => 'Halloween',
				'discount_percent' => 20,
				'start_date'       => strtotime( 'tomorrow' ),
				'end_date'         => strtotime( 'next month' ),
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnTrueWhenPromoActive' => [
		'data'     => json_decode( json_encode( [
			'promo' => [
				'name'             => 'Halloween',
				'discount_percent' => 20,
				'start_date'       => strtotime( 'last week' ),
				'end_date'         => strtotime( 'next week' ),
			],
		] ) ),
		'expected' => true,
	],
];
