<?php

$licenses = [
	'licenses' => [
		'single'   => [
			'prices'   => [],
			'websites' => 1,
		],
		'plus'     => [
			'prices'   => [],
			'websites' => 3,
		],
		'infinite' => [
			'prices'   => [],
			'websites' => 'unlimited',
		],
		'multi500' => [
			'prices'   => [ 'regular' => 499.95 ],
			'websites' => 500,
		],
	],
];

return [
	'testShouldReturnNullWhenLicensesNotSet' => [
		'data'     => json_decode( json_encode( [] ) ),
		'websites' => 500,
		'expected' => null,
	],
	'testShouldReturnNullWhenNoLicenseMatches' => [
		'data'     => json_decode( json_encode( $licenses ) ),
		'websites' => 200,
		'expected' => null,
	],
	'testShouldReturnNullWhenWebsitesIsUnlimited' => [
		'data'     => json_decode( json_encode( $licenses ) ),
		'websites' => -1,
		'expected' => null,
	],
	'testShouldReturnNullWhenWebsitesIsZero' => [
		'data'     => json_decode( json_encode( $licenses ) ),
		'websites' => 0,
		'expected' => null,
	],
	'testShouldReturnMatchingLicense' => [
		'data'     => json_decode( json_encode( $licenses ) ),
		'websites' => 500,
		'expected' => (object) [
			'prices'   => (object) [ 'regular' => 499.95 ],
			'websites' => 500,
		],
	],
];
