<?php

$licenses = [
	'licenses' => [
		'single'   => [
			'prices'   => [],
			'websites' => 1,
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
		'type'     => 'multi500',
		'expected' => null,
	],
	'testShouldReturnNullWhenTypeIsEmpty' => [
		'data'     => json_decode( json_encode( $licenses ) ),
		'type'     => '',
		'expected' => null,
	],
	'testShouldReturnNullWhenNoLicenseMatches' => [
		'data'     => json_decode( json_encode( $licenses ) ),
		'type'     => 'multi200',
		'expected' => null,
	],
	'testShouldReturnNullWhenEntryIsNotAnObject' => [
		'data'     => json_decode( json_encode( [ 'licenses' => [ 'multi500' => 'invalid' ] ] ) ),
		'type'     => 'multi500',
		'expected' => null,
	],
	'testShouldReturnMatchingLicense' => [
		'data'     => json_decode( json_encode( $licenses ) ),
		'type'     => 'multi500',
		'expected' => (object) [
			'prices'   => (object) [ 'regular' => 499.95 ],
			'websites' => 500,
		],
	],
];
