<?php
return [
	// Delete button is disabled while subscription is being created.
	'testShouldDisableDeleteButtonWhenSubscriptionLoading' => [
		'config'   => [
			'is_subscription_loading' => true,
			'cdn'                     => true,
			'has_active_subscription' => true,
			'is_free'                 => true,
			'is_license_invalid'      => false,
		],
		'expected' => true,
	],

	// Delete button is disabled when the CDN is paused.
	'testShouldDisableDeleteButtonWhenCdnPaused'           => [
		'config'   => [
			'is_subscription_loading' => false,
			'cdn'                     => false,
			'has_active_subscription' => true,
			'is_free'                 => true,
			'is_license_invalid'      => false,
		],
		'expected' => true,
	],

	// Delete button is disabled when the user has no active subscription.
	'testShouldDisableDeleteButtonWhenNoActiveSubscription' => [
		'config'   => [
			'is_subscription_loading' => false,
			'cdn'                     => true,
			'has_active_subscription' => false,
			'is_free'                 => true,
			'is_license_invalid'      => false,
		],
		'expected' => true,
	],

	// Delete button is disabled when the free plan has an invalid WP Rocket licence.
	'testShouldDisableDeleteButtonWhenLicenseInvalid'      => [
		'config'   => [
			'is_subscription_loading' => false,
			'cdn'                     => true,
			'has_active_subscription' => true,
			'is_free'                 => true,
			'is_license_invalid'      => true,
		],
		'expected' => true,
	],

	// Delete button is enabled when the subscription is active and the CDN is not paused.
	'testShouldEnableDeleteButtonWhenActive'               => [
		'config'   => [
			'is_subscription_loading' => false,
			'cdn'                     => true,
			'has_active_subscription' => true,
			'is_free'                 => false,
			'is_license_invalid'      => false,
		],
		'expected' => false,
	],
];
