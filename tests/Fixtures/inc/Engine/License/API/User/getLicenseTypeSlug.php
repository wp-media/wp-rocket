<?php

return [
	'testShouldReturnEmptyStringWhenNotObject' => [
		'data'     => [],
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenLicenceNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
		] ) ),
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenTypeNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID'      => 1,
			'licence' => [ 'name' => 'Multi 500' ],
		] ) ),
		'expected' => '',
	],
	'testShouldReturnTypeWhenSetEvenIfAccountIsUnlimited' => [
		'data'     => json_decode( json_encode( [
			'ID'              => 1,
			'licence_account' => -1,
			'licence'         => [ 'type' => 'multi500' ],
		] ) ),
		'expected' => 'multi500',
	],
];
