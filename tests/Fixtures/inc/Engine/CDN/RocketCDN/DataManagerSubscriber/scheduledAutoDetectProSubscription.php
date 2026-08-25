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
	'testShouldNotChangeCdnStateConclusiveNotPaid'       => [
		'config'   => [
			'attempt'             => 3,
			'website_search_code' => 200,
			'website_search_body' => [
				'subscription_status'    => 'cancelled',
				'subscription_plan_type' => 'free',
			],
			'pre_set_cdn_state'   => 'nothing',
		],
		'expected' => [
			'cdn_state'              => 'nothing',
			'failed_transient'       => false,
			'scheduled_next_attempt' => null,
		],
	],
];
