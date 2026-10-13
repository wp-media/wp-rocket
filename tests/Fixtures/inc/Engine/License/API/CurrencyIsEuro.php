<?php

return [
	'testShouldReturnTrueForEUR' => [
		'currency' => 'EUR',
		'expected' => true,
	],
	'testShouldReturnFalseForUSD' => [
		'currency' => 'USD',
		'expected' => false,
	],
	'testShouldReturnFalseForOtherCurrency' => [
		'currency' => 'GBP',
		'expected' => false,
	],
	'testShouldReturnFalseForEmptyString' => [
		'currency' => '',
		'expected' => false,
	],
	'testShouldReturnFalseForLowercaseEur' => [
		'currency' => 'eur',
		'expected' => false,
	],
];
