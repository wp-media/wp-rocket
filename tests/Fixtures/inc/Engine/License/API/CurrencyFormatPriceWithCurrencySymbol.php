<?php

return [
	'testShouldPrefixDollarSignForUSD' => [
		'config'   => [
			'price'    => '49',
			'currency' => 'USD',
		],
		'expected' => '$49',
	],
	'testShouldSuffixEuroSignForEUR' => [
		'config'   => [
			'price'    => '49',
			'currency' => 'EUR',
		],
		'expected' => '49€',
	],
	'testShouldAddSpaceWhenWithSpaceIsTrue' => [
		'config'   => [
			'price'     => '49',
			'currency'  => 'USD',
			'with_space' => true,
		],
		'expected' => '$ 49',
	],
	'testShouldAddSpaceForEURWhenWithSpaceIsTrue' => [
		'config'   => [
			'price'     => '49',
			'currency'  => 'EUR',
			'with_space' => true,
		],
		'expected' => '49 €',
	],
	'testShouldWrapPriceInSpanWhenWrapSpanIsPrice' => [
		'config'   => [
			'price'       => '49',
			'currency'    => 'USD',
			'wrap_span'   => 'price',
			'span_classes' => [ 'price' => 'price-class' ],
		],
		'expected' => '$<span class="price-class">49</span>',
	],
	'testShouldWrapCurrencyInSpanWhenWrapSpanIsCurrency' => [
		'config'   => [
			'price'       => '49',
			'currency'    => 'USD',
			'wrap_span'   => 'currency',
			'span_classes' => [ 'currency' => 'currency-class' ],
		],
		'expected' => '<span class="currency-class">$</span>49',
	],
	'testShouldWrapBothInSpanWhenWrapSpanIsBoth' => [
		'config'   => [
			'price'       => '49',
			'currency'    => 'USD',
			'wrap_span'   => 'both',
			'span_classes' => [
				'price'    => 'price-class',
				'currency' => 'currency-class',
			],
		],
		'expected' => '<span class="currency-class">$</span><span class="price-class">49</span>',
	],
	'testShouldWrapBothForEURWithSpan' => [
		'config'   => [
			'price'       => '49',
			'currency'    => 'EUR',
			'wrap_span'   => 'both',
			'span_classes' => [
				'price'    => 'price-class',
				'currency' => 'currency-class',
			],
		],
		'expected' => '<span class="price-class">49</span><span class="currency-class">€</span>',
	],
	'testShouldUseDefaultSymbolForUnknownCurrency' => [
		'config'   => [
			'price'    => '99',
			'currency' => 'GBP',
		],
		'expected' => '$99',
	],
];
