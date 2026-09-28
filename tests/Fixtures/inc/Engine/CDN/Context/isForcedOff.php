<?php

return [
	'not forced off when nothing matches'                        => [
		'config'   => [],
		'expected' => [
			'is_forced_off' => false,
			'reason'        => null,
		],
	],
	'paid cancelled but still in grace period'                   => [
		'config'   => [
			'is_paid'            => true,
			'is_in_grace_period' => true,
		],
		'expected' => [
			'is_forced_off' => true,
			'reason'        => 'pro_cancelled_in_grace_period',
		],
	],
	'paid cancelled outside grace period'                        => [
		'config'   => [
			'is_paid'                          => true,
			'is_cancelled_outside_grace_period' => true,
		],
		'expected' => [
			'is_forced_off' => true,
			'reason'        => 'pro_cancelled_outside_grace',
		],
	],
	'free with expired license'                                  => [
		'config'   => [
			'is_free'            => true,
			'is_license_invalid' => true,
		],
		'expected' => [
			'is_forced_off' => true,
			'reason'        => 'license_expired',
		],
	],
	'free with revoked license reports license_banned'           => [
		'config'   => [
			'is_free'            => true,
			'is_license_invalid' => true,
			'is_revoked'         => true,
		],
		'expected' => [
			'is_forced_off' => true,
			'reason'        => 'license_banned',
		],
	],
	'cancelled outside grace period with invalid license'        => [
		'config'   => [
			'is_cancelled_outside_grace_period' => true,
			'is_license_invalid'                => true,
		],
		'expected' => [
			'is_forced_off' => true,
			'reason'        => 'pro_cancelled_outside_grace',
		],
	],
];
