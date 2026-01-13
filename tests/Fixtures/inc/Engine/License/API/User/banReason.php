<?php

return [
	'testShouldReturnEmptyStringWhenNotObject' => [
		'data'     => [],
		'expected' => false,
	],
	'testShouldReturnEmptyStringWhenLicencePropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
		] ) ),
		'expected' => false,
	],
	'testShouldReturnEmptyStringWhenBannedPropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
			'licence' => (object) [],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnNotEmptyString' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'licence' => (object) [
				'ban_reason' => 'ANY_REASON',
			],
		] ) ),
		'expected' => true,
	],
];
