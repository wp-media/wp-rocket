<?php

return [
	'testShouldReturnOngoingActivationFreeWhenLoading'      => [
		'config'   => [
			'is_subscription_creation_loading' => true,
		],
		'expected' => 'ongoing_activation_free',
	],
	'testShouldReturnProWhenInGracePeriod'                  => [
		'config'   => [
			'is_in_grace_period' => true,
		],
		'expected' => 'pro',
	],
	'testShouldReturnProWhenActivePaidSubscription'         => [
		'config'   => [
			'has_active_subscription' => true,
			'is_paid'                 => true,
		],
		'expected' => 'pro',
	],
	'testShouldReturnFreeWhenActiveFreeSubscription'        => [
		'config'   => [
			'has_active_subscription' => true,
			'is_paid'                 => false,
			'is_free'                 => true,
		],
		'expected' => 'free',
	],
	'testShouldReturnNothingWhenNoSubscription'             => [
		'config'   => [
			'has_active_subscription' => false,
		],
		'expected' => 'nothing',
	],

	// TC-Q3: active subscription with an unexpected plan_type (neither 'free' nor 'paid') falls through to Nothing.
	'testShouldReturnNothingWhenActiveSubscriptionHasUnexpectedPlanType' => [
		'config'   => [
			'has_active_subscription' => true,
			'is_paid'                 => false,
			'is_free'                 => false,
		],
		'expected' => 'nothing',
	],
];
