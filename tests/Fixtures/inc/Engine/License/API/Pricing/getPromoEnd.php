<?php

return [
	'testShouldReturnZeroWhenNotObject' => [
		'data'     => [
			'promo' => [],
		],
		'expected' => 0,
	],
	'testShouldReturnZeroWhenPromoNotSet' => [
		'data'     => json_decode( json_encode( [
			'licenses' => [],
		] ) ),
		'expected' => 0,
	],
	'testShouldReturnZeroWhenPromoEndDatePropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'promo' => [
				'name'             => 'Halloween',
				'discount_percent' => 20,
				'start_date'       => 1603756800,
			],
		] ) ),
		'expected' => 0,
	],
	'testShouldReturnIntWhenEndDateSet' => [
		'data'     => json_decode( json_encode( [
			'promo' => [
				'name'             => 'Halloween',
				'discount_percent' => 20,
				'start_date'       => strtotime( 'last week' ),
				'end_date'         => 12345,
			],
		] ) ),
		'expected' => 12345,
	],
];
