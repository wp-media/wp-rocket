<?php

return [
	'shouldReturnOriginalValueWhenNotRocketcdnDriver'              => [
		'config'   => [
			'is_rocketcdn'           => false,
			'is_free'                => false,
			'is_license_invalid'     => false,
			'transient'              => false,
			'has_inactive_subscription' => false,
			'cdn'                    => 1,
		],
		'expected' => 1,
	],
	'shouldReturnFalseWhenFreeAndLicenseInvalid'                   => [
		'config'   => [
			'is_rocketcdn'           => true,
			'is_free'                => true,
			'is_license_invalid'     => true,
			'transient'              => false,
			'has_inactive_subscription' => false,
			'cdn'                    => 1,
		],
		'expected' => false,
	],
	'shouldReturnOriginalValueWhenFreeButLicenseValid'             => [
		'config'   => [
			'is_rocketcdn'           => true,
			'is_free'                => true,
			'is_license_invalid'     => false,
			'transient'              => false,
			'has_inactive_subscription' => false,
			'cdn'                    => 1,
		],
		'expected' => 1,
	],
	'shouldReturnFalseWhenTransientExistsAndSubscriptionInactive'  => [
		'config'   => [
			'is_rocketcdn'           => true,
			'is_free'                => false,
			'is_license_invalid'     => false,
			'transient'              => [ 'subscription_status' => 'cancelled' ],
			'has_inactive_subscription' => true,
			'cdn'                    => 1,
		],
		'expected' => false,
	],
	'shouldReturnOriginalValueWhenTransientExistsButSubscriptionActive' => [
		'config'   => [
			'is_rocketcdn'           => true,
			'is_free'                => false,
			'is_license_invalid'     => false,
			'transient'              => [ 'subscription_status' => 'running' ],
			'has_inactive_subscription' => false,
			'cdn'                    => 1,
		],
		'expected' => 1,
	],
	'shouldReturnOriginalValueWhenNoTransient'                     => [
		'config'   => [
			'is_rocketcdn'           => true,
			'is_free'                => false,
			'is_license_invalid'     => false,
			'transient'              => false,
			'has_inactive_subscription' => false,
			'cdn'                    => 1,
		],
		'expected' => 1,
	],
];
