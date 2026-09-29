<?php

$future_expiration = strtotime( '+10 days' );
$past_expiration    = strtotime( '-2 days' );
$delay              = 5 * MINUTE_IN_SECONDS;

return [
	'testShouldScheduleWhenExpirationIsInTheFutureAndNotYetScheduled' => [
		'config' => [
			'is_trial_customer'    => true,
			'checks_expiration'    => true,
			'license_expiration'   => $future_expiration,
			'checks_schedule'      => true,
			'next_scheduled'       => false,
			'should_schedule'      => true,
			'expected_target_time' => $future_expiration + $delay,
		],
	],
	'testShouldNotScheduleWhenAlreadyScheduled' => [
		'config' => [
			'is_trial_customer'    => true,
			'checks_expiration'    => true,
			'license_expiration'   => $future_expiration,
			'checks_schedule'      => true,
			'next_scheduled'       => $future_expiration + $delay,
			'should_schedule'      => false,
			'expected_target_time' => null,
		],
	],
	'testShouldNotScheduleWhenExpirationIsAlreadyInThePast' => [
		'config' => [
			'is_trial_customer'    => true,
			'checks_expiration'    => true,
			'license_expiration'   => $past_expiration,
			'checks_schedule'      => false,
			'next_scheduled'       => null,
			'should_schedule'      => false,
			'expected_target_time' => null,
		],
	],
	'testShouldNotScheduleWhenCustomerIsNotOnTrial' => [
		'config' => [
			'is_trial_customer'    => false,
			'checks_expiration'    => false,
			'license_expiration'   => null,
			'checks_schedule'      => false,
			'next_scheduled'       => null,
			'should_schedule'      => false,
			'expected_target_time' => null,
		],
	],
	'testShouldNotScheduleWhenExpirationIsUnknown' => [
		'config' => [
			'is_trial_customer'    => true,
			'checks_expiration'    => true,
			'license_expiration'   => 0,
			'checks_schedule'      => false,
			'next_scheduled'       => null,
			'should_schedule'      => false,
			'expected_target_time' => null,
		],
	],
];
