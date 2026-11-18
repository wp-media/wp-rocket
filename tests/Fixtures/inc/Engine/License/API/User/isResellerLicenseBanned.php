<?php

return [
	'testShouldReturnTrueWhenResellerRevokedAndBannedReason'  => [
		'data'     => json_decode( json_encode( [
			'is_reseller' => true,
			'licence'     => (object) [
				'is_revoked'                => true,
				'plugin_updates_ban_reason' => 'BANNED_WEBSITE',
			],
		] ) ),
		'expected' => true,
	],
	'testShouldReturnFalseWhenResellerRevokedForOtherReason'  => [
		'data'     => json_decode( json_encode( [
			'is_reseller' => true,
			'licence'     => (object) [
				'is_revoked'                => true,
				'plugin_updates_ban_reason' => 'NON_PAYMENT',
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenResellerNotRevoked'             => [
		'data'     => json_decode( json_encode( [
			'is_reseller' => true,
			'licence'     => (object) [
				'is_revoked' => false,
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenNonResellerBannedReason'        => [
		'data'     => json_decode( json_encode( [
			'is_reseller' => false,
			'licence'     => (object) [
				'is_revoked'                => true,
				'plugin_updates_ban_reason' => 'BANNED_WEBSITE',
			],
		] ) ),
		'expected' => false,
	],
	'testShouldReturnFalseWhenResellerRevokedWithEmptyReason' => [
		'data'     => json_decode( json_encode( [
			'is_reseller' => true,
			'licence'     => (object) [
				'is_revoked'                => true,
				'plugin_updates_ban_reason' => '',
			],
		] ) ),
		'expected' => false,
	],
];
