<?php

return [
	'empty when nothing forces it off'               => [
		'config'   => [
			'is_subscription_loading' => false,
			'is_rocketcdn'            => true,
			'is_free'                 => true,
			'is_license_invalid'      => false,
		],
		'expected' => '',
	],
	'activation-in-progress copy first'              => [
		'config'   => [
			'is_subscription_loading' => true,
		],
		'expected' => 'RocketCDN is currently being activated. Please wait, this should only take a moment.',
	],
	'expired-licence copy second'                    => [
		'config'   => [
			'is_subscription_loading' => false,
			'is_rocketcdn'            => true,
			'is_free'                 => true,
			'is_license_invalid'      => true,
		],
		'expected' => 'Renew to use RocketCDN Free.',
	],
	'banned-reseller copy third'                     => [
		'config'   => [
			'is_subscription_loading'    => false,
			'is_rocketcdn'               => true,
			'is_free'                    => true,
			'is_license_invalid'         => false,
			'is_reseller_license_banned' => true,
		],
		'expected' => 'Contact support to find out how to restore access.',
	],
	'loading takes precedence over expired'          => [
		'config'   => [
			'is_subscription_loading' => true,
			'is_rocketcdn'            => true,
			'is_free'                 => true,
			'is_license_invalid'      => true,
		],
		'expected' => 'RocketCDN is currently being activated. Please wait, this should only take a moment.',
	],
	// should_display_licence_expired_notice() deliberately excludes banned
	// resellers (`! is_reseller_license_banned()`), so a banned + invalid
	// licence always falls through to the banned copy, never the expired
	// one - same precedent as should_reject_rocketcdn_activation().
	'banned wins even when licence is also invalid'  => [
		'config'   => [
			'is_subscription_loading'    => false,
			'is_rocketcdn'               => true,
			'is_free'                    => true,
			'is_license_invalid'         => true,
			'is_reseller_license_banned' => true,
		],
		'expected' => 'Contact support to find out how to restore access.',
	],
	'forced-paused copy fourth, for a cancelled paid plan' => [
		'config'   => [
			'is_subscription_loading' => false,
			'is_rocketcdn'            => true,
			'is_free'                 => false,
			'is_license_invalid'      => false,
			'is_paid'                 => true,
			'is_in_grace_period'      => true,
		],
		'expected' => 'Cancelling your subscription.',
	],
];
