<?php

$future_expiration = strtotime( '+10 days' );
$past_expiration    = strtotime( '-2 days' );
$delay              = 5 * MINUTE_IN_SECONDS;

return [
	'testShouldScheduleWhenExpirationIsInTheFutureAndNotYetScheduled' => [
		'config' => [
			'can_manage_options'   => true,
			'checks_trial'         => true,
			'is_trial_customer'    => true,
			'checks_schedule'      => true,
			'next_scheduled'       => false,
			'checks_expiration'    => true,
			'license_expiration'   => $future_expiration,
			'should_schedule'      => true,
			'expected_target_time' => $future_expiration + $delay,
		],
	],
	'testShouldNotScheduleWhenUserCannotManageOptions' => [
		'config' => [
			'can_manage_options'   => false,
			'checks_trial'         => false,
			'is_trial_customer'    => null,
			'checks_schedule'      => false,
			'next_scheduled'       => null,
			'checks_expiration'    => false,
			'license_expiration'   => null,
			'should_schedule'      => false,
			'expected_target_time' => null,
		],
	],
	'testShouldNotScheduleWhenCustomerIsNotOnTrial' => [
		'config' => [
			'can_manage_options'   => true,
			'checks_trial'         => true,
			'is_trial_customer'    => false,
			'checks_schedule'      => false,
			'next_scheduled'       => null,
			'checks_expiration'    => false,
			'license_expiration'   => null,
			'should_schedule'      => false,
			'expected_target_time' => null,
		],
	],
	'testShouldNotCheckExpirationWhenAlreadyScheduled' => [
		'config' => [
			'can_manage_options'   => true,
			'checks_trial'         => true,
			'is_trial_customer'    => true,
			'checks_schedule'      => true,
			'next_scheduled'       => $future_expiration + $delay,
			'checks_expiration'    => false,
			'license_expiration'   => null,
			'should_schedule'      => false,
			'expected_target_time' => null,
		],
	],
	'testShouldNotScheduleWhenExpirationIsAlreadyInThePast' => [
		'config' => [
			'can_manage_options'   => true,
			'checks_trial'         => true,
			'is_trial_customer'    => true,
			'checks_schedule'      => true,
			'next_scheduled'       => false,
			'checks_expiration'    => true,
			'license_expiration'   => $past_expiration,
			'should_schedule'      => false,
			'expected_target_time' => null,
		],
	],
	'testShouldNotScheduleWhenExpirationIsUnknown' => [
		'config' => [
			'can_manage_options'   => true,
			'checks_trial'         => true,
			'is_trial_customer'    => true,
			'checks_schedule'      => true,
			'next_scheduled'       => false,
			'checks_expiration'    => true,
			'license_expiration'   => 0,
			'should_schedule'      => false,
			'expected_target_time' => null,
		],
	],
];
