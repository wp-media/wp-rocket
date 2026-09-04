<?php
return [
	'testShouldRescheduleInconclusiveWithAttemptsLeft'        => [
		'config'   => [
			'attempt'                  => 2,
			'subscription_status_code' => 404,
		],
		'expected' => [
			'failed_transient'       => false,
			'scheduled_next_attempt' => 1,
		],
	],
	'testShouldSetFailureTransientInconclusiveAttemptsExhausted' => [
		'config'   => [
			'attempt'                  => 1,
			'subscription_status_code' => 404,
		],
		'expected' => [
			'failed_transient'       => true,
			'scheduled_next_attempt' => null,
		],
	],
	'testShouldClearFailureAndCancelPendingJobWhenConclusive' => [
		'config'   => [
			'attempt'                  => 2,
			'subscription_status_code' => 200,
			'pre_set_failed_transient' => true,
			'pre_scheduled_attempt'    => 1,
		],
		'expected' => [
			'failed_transient'       => false,
			'scheduled_next_attempt' => null,
		],
	],
	'testShouldClearFailureAndCancelPendingJobWhenAccountHasNoToken' => [
		'config'   => [
			'attempt'                  => 2,
			// A successful user-endpoint response with no `rocketcdn` token is a conclusive
			// "never engaged with RocketCDN" answer — resolves the same way a 200 does.
			'user_data_code'           => 200,
			'user_data_body'           => [],
			'pre_set_failed_transient' => true,
			'pre_scheduled_attempt'    => 1,
		],
		'expected' => [
			'failed_transient'       => false,
			'scheduled_next_attempt' => null,
		],
	],
];
