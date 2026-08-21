<?php
return [
	'testShouldRescheduleAutoDetectInconclusiveWithAttemptsLeft'  => [
		'config'   => [
			'attempt'             => 3,
			'website_search_code' => 404,
		],
		'expected' => [
			'cdn_state'              => null,
			'failed_transient'       => false,
			'scheduled_next_attempt' => 2,
		],
	],
	'testShouldSetFailureTransientInconclusiveAttemptsExhausted' => [
		'config'   => [
			'attempt'             => 1,
			'website_search_code' => 404,
		],
		'expected' => [
			'cdn_state'              => null,
			'failed_transient'       => true,
			'scheduled_next_attempt' => null,
		],
	],
	'testShouldSetCdnStateConclusiveRunningPaid'         => [
		'config'   => [
			'attempt'             => 3,
			'website_search_code' => 200,
			'website_search_body' => [
				'subscription_status'    => 'running',
				'subscription_plan_type' => 'paid',
			],
		],
		'expected' => [
			'cdn_state'              => 'rocketcdn_paid',
			'failed_transient'       => false,
			'scheduled_next_attempt' => null,
		],
	],
];
