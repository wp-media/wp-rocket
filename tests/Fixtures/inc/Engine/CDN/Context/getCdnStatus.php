<?php

return [
	'testShouldReturnOngoingActivationWhenSubscriptionLoading'      => [
		'config'   => [
			'is_subscription_creation_loading' => true,
		],
		'expected' => 'ongoing_activation',
	],
	'testShouldReturnForcedOffWhenPaidInGracePeriod'                => [
		'config'   => [
			'is_paid'            => true,
			'is_in_grace_period' => true,
		],
		'expected' => 'forced_off',
	],
	'testShouldReturnForcedOffWhenPaidCancelledOutsideGracePeriod'  => [
		'config'   => [
			'is_paid'                          => true,
			'is_cancelled_outside_grace_period' => true,
		],
		'expected' => 'forced_off',
	],
	'testShouldReturnForcedOffWhenFreeWithInvalidLicense'           => [
		'config'   => [
			'is_free'            => true,
			'is_license_invalid' => true,
		],
		'expected' => 'forced_off',
	],
	'testShouldReturnForcedOffWhenResellerLicenseBanned'            => [
		'config'   => [
			'is_reseller_license_banned' => true,
		],
		'expected' => 'forced_off',
	],
	'testShouldReturnActiveWhenModeAppliedAndNotForcedOff'          => [
		'config'   => [
			'cdn_state' => 'rocketcdn_free',
		],
		'expected' => 'active',
	],
	'testShouldReturnInactiveWhenModeNoneAndNotForcedOff'           => [
		'config'   => [
			'cdn_state' => 'nothing',
		],
		'expected' => 'inactive',
	],
];
