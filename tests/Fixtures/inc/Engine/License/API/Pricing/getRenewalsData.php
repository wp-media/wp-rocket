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
			'licenses' => [],
		] ) ),
		'expected' => null,
	],
	'testShouldReturnObjectWhenSet' => [
		'data'     => json_decode( json_encode( [
			'renewals' => [
				'extra_days'       => 90,
				'grandfather_date' => 1567296000,
				'discount_percent' => [
					'is_grandfather'  => 50,
					'not_grandfather' => 30,
					'is_expired'      => 20,
				],
			],
		] ) ),
		'expected' => json_decode( json_encode( [
			'extra_days'       => 90,
			'grandfather_date' => 1567296000,
			'discount_percent' => [
				'is_grandfather'  => 50,
				'not_grandfather' => 30,
				'is_expired'      => 20,
			],
		] ) ),
	],
];
