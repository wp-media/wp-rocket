<?php

return [
	'testShouldReturnUSDWhenCurrencyNotSet' => [
		'data'     => (object) [],
		'expected' => 'USD',
	],
	'testShouldReturnUSDWhenDataIsNotObject' => [
		'data'     => [],
		'expected' => 'USD',
	],
	'testShouldReturnCurrencyWhenSet' => [
		'data'     => json_decode( json_encode( [
			'currency' => 'EUR',
		] ) ),
		'expected' => 'EUR',
	],
	'testShouldReturnGBPWhenSet' => [
		'data'     => json_decode( json_encode( [
			'currency' => 'GBP',
		] ) ),
		'expected' => 'GBP',
	],
];
