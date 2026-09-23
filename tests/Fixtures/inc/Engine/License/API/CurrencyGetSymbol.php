<?php

return [
	'testShouldReturnDollarSignForUSD' => [
		'currency' => 'USD',
		'expected' => '$',
	],
	'testShouldReturnEuroSignForEUR' => [
		'currency' => 'EUR',
		'expected' => '€',
	],
	'testShouldReturnDollarSignForUnknownCurrency' => [
		'currency' => 'GBP',
		'expected' => '$',
	],
	'testShouldReturnDollarSignForEmptyCurrency' => [
		'currency' => '',
		'expected' => '$',
	],
	'testShouldHandleLowercaseCurrencyCode' => [
		'currency' => 'eur',
		'expected' => '€',
	],
	'testShouldHandleMixedCaseCurrencyCode' => [
		'currency' => 'Usd',
		'expected' => '$',
	],
	'testShouldHandleCurrencyWithSpaces' => [
		'currency' => ' EUR ',
		'expected' => '€',
	],
];
