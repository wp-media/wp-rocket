<?php

return [
	'testShouldReturnEmptyStringWhenNotObject' => [
		'data'     => [],
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenLicencePropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
		] ) ),
		'expected' => '',
	],
	'testShouldReturnEmptyStringWhenBannedPropertyNotSet' => [
		'data'     => json_decode( json_encode( [
			'ID' => 1,
			'licence' => (object) [],
		] ) ),
		'expected' => '',
	],
	'testShouldReturnNotEmptyString' => [
		'data'     => json_decode( json_encode( [
			'ID'         => 1,
			'licence' => (object) [
				'ban_reason' => 'ANY_REASON',
			],
		] ) ),
		'expected' => 'ANY_REASON',
	],
];
