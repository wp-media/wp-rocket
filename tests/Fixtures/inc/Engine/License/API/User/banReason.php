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
	'testShouldReturnEmptyStringWhenRevokedPropertyNotSet' => [
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
				'plugin_updates_ban_reason' => 'ANY_REASON',
			],
		] ) ),
		'expected' => 'ANY_REASON',
	],
];
