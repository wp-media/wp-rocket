<?php
return [
	'testShouldNotScheduleJobWhenConclusive'         => [
		'config'   => [
			'token'                    => true,
			'subscription_status_code' => 200,
		],
		'expected' => [
			'scheduled_attempt' => null,
		],
	],
	'testShouldScheduleJobWhenApiCallInconclusive'    => [
		'config'   => [
			'token'                    => true,
			'subscription_status_code' => 404,
		],
		'expected' => [
			'scheduled_attempt' => 2,
		],
	],
	'testShouldScheduleJobWhenNoTokenYet'             => [
		'config'   => [
			'token' => false,
		],
		'expected' => [
			'scheduled_attempt' => 2,
		],
	],
	'testShouldNotScheduleJobWhenAccountHasNoRocketCdnToken' => [
		'config'   => [
			'token'          => false,
			// A successful user-endpoint response with no `rocketcdn` token is a conclusive
			// "never engaged with RocketCDN" answer, unlike the failed-call case above.
			'user_data_code' => 200,
			'user_data_body' => [],
		],
		'expected' => [
			'scheduled_attempt' => null,
		],
	],
];
