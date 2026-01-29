<?php

return [
	'testShouldReturnFalseWhenNotObject' => [
		'data'     => [],
		'expected' => false,
	],
	'testShouldReturnFalseWhenLicencePropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenRevokedPropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
			'licence' => (object) [],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenNotRevoked' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'licence' => (object) [
				'is_revoked' => false,
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenRevokedIsZero' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'licence' => (object) [
				'is_revoked' => 0,
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnTrueWhenRevoked' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'licence' => (object) [
				'is_revoked' => true,
			],
		] ) ),
		'expected' => true,
	],
	'testShouldReturnTrueWhenRevokedIsOne' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'licence' => (object) [
				'is_revoked' => 1,
			],
		] ) ),
		'expected' => true,
	],
];
