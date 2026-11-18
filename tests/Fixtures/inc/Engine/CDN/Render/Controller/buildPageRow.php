<?php
declare(strict_types=1);

use WP_Rocket\Engine\CDN\Context;

return [
	// Delete button is disabled while the subscription is being created.
	'testShouldDisableDeleteButtonWhenSubscriptionLoading' => [
		'config'   => [
			'is_subscription_loading' => true,
			'applied_cdn_state'       => Context::ROCKETCDN_TYPE,
		],
		'expected' => true,
	],

	// Delete button is disabled when the user paused RocketCDN.
	'testShouldDisableDeleteButtonWhenCdnPaused'           => [
		'config'   => [
			'applied_cdn_state' => Context::CDN_STATE_NOTHING,
		],
		'expected' => true,
	],

	// Delete button is disabled when another CDN is active, which also pauses RocketCDN.
	'testShouldDisableDeleteButtonWhenOtherCdnActive'      => [
		'config'   => [
			'applied_cdn_state' => Context::BYOCDN_TYPE,
		],
		'expected' => true,
	],

	// Delete button is disabled when the free plan has an invalid WP Rocket licence.
	'testShouldDisableDeleteButtonWhenLicenseInvalid'      => [
		'config'   => [
			'applied_cdn_state'  => Context::ROCKETCDN_TYPE,
			'is_rocketcdn'       => true,
			'is_free'            => true,
			'is_license_invalid' => true,
		],
		'expected' => true,
	],

	// Delete button is disabled when the user has no active subscription.
	'testShouldDisableDeleteButtonWhenNoActiveSubscription' => [
		'config'   => [
			'applied_cdn_state'       => Context::ROCKETCDN_TYPE,
			'has_active_subscription' => false,
		],
		'expected' => true,
	],

	// Delete button is enabled when RocketCDN is active and nothing blocks it.
	'testShouldEnableDeleteButtonWhenActive'               => [
		'config'   => [
			'applied_cdn_state'       => Context::ROCKETCDN_TYPE,
			'is_rocketcdn'            => true,
			'is_free'                 => true,
			'is_license_invalid'      => false,
			'has_active_subscription' => true,
		],
		'expected' => false,
	],
];
