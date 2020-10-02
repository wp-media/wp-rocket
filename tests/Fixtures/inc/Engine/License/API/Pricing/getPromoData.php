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
			'promo' => [
				'name'             => 'Halloween',
				'discount_percent' => 20,
				'start_date'       => 1603756800,
				'end_date'         => 1604361600,
			],
		] ) ),
		'expected' => (object) [
			'name'             => 'Halloween',
			'discount_percent' => 20,
			'start_date'       => 1603756800,
			'end_date'         => 1604361600,
		],
	],
];
