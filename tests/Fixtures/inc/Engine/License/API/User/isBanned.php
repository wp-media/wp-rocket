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
	'testShouldReturnFalseWhenBannedPropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
			'licence' => (object) [],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenNotBanned' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'licence' => (object) [
				'is_banned' => false,
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenBannedIsZero' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'licence' => (object) [
				'is_banned' => 0,
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnTrueWhenBanned' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'licence' => (object) [
				'is_banned' => true,
			],
		] ) ),
		'expected' => true,
	],
	'testShouldReturnTrueWhenBannedIsOne' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'licence' => (object) [
				'is_banned' => 1,
			],
		] ) ),
		'expected' => true,
	],
];
