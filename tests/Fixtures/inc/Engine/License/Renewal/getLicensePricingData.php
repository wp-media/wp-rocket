<?php

$single   = (object) [ 'name' => 'single' ];
$plus     = (object) [ 'name' => 'plus' ];
$infinite = (object) [ 'name' => 'infinite' ];
$multi500 = (object) [ 'name' => 'multi500', 'prices' => (object) [ 'renewal' => (object) [ 'not_grandfather' => 499.95 ] ] ];

return [
	'testShouldReturnTypePricingWhenMultiLicenseReportsUnlimitedAccount' => [
		'config'   => [
			'slug'          => 'multi500',
			'license_type'  => -1,
			'type_pricing'  => $multi500,
		],
		'expected' => $multi500,
	],
	'testShouldReturnInfinitePricingWhenTypeHasNoPricing' => [
		'config'   => [
			'slug'          => 'multi200',
			'license_type'  => -1,
			'type_pricing'  => null,
		],
		'expected' => $infinite,
	],
	'testShouldReturnInfinitePricingWhenTypeHasNoRenewalPrices' => [
		'config'   => [
			'slug'          => 'multi500',
			'license_type'  => -1,
			'type_pricing'  => (object) [ 'name' => 'multi500', 'prices' => (object) [ 'regular' => 499.95 ] ],
		],
		'expected' => $infinite,
	],
	'testShouldReturnSinglePricingWhenNoTypeAndSingleLicense' => [
		'config'   => [
			'slug'          => '',
			'license_type'  => 1,
			'type_pricing'  => null,
		],
		'expected' => $single,
	],
	'testShouldReturnPlusPricingWhenNoTypeAndPlusLicense' => [
		'config'   => [
			'slug'          => '',
			'license_type'  => 3,
			'type_pricing'  => null,
		],
		'expected' => $plus,
	],
];
