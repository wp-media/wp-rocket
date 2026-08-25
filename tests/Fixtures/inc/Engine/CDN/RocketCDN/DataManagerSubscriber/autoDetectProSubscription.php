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
];
