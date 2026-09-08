<?php

return [
	'testShouldCallWPNonceAysWhenNonceIsMissing'                                => [
		'config'   => [
			'user_role' => 'administrator',
			'nonce'     => 'missing',
		],
		'expected' => [
			'exception_message' => 'The link you followed has expired.',
		],
	],

	'testShouldCallWPNonceAysWhenNonceIsInvalid'                                => [
		'config'   => [
			'user_role' => 'administrator',
			'nonce'     => 'invalid',
		],
		'expected' => [
			'exception_message' => 'The link you followed has expired.',
		],
	],

	'testShouldCallWPDieWhenCurrentUserCant'                                    => [
		'config'   => [
			'user_role' => 'contributor',
			'nonce'     => 'valid',
		],
		'expected' => [
			'can_manage_options' => false,
		],
	],

	'testShouldClearFailedTransientAndScheduleJobWhenConclusive'                => [
		'config'   => [
			'user_role'                      => 'administrator',
			'nonce'                          => 'valid',
			'pro_detection_failed_transient' => true,
			'token'                          => true,
			'subscription_status_code'       => 200,
		],
		'expected' => [
			'can_manage_options'       => true,
			'failed_transient_cleared' => true,
			'job_scheduled'            => true,
		],
	],

	'testShouldClearFailedTransientAndScheduleJobWhenInconclusive'              => [
		'config'   => [
			'user_role'                      => 'administrator',
			'nonce'                          => 'valid',
			'pro_detection_failed_transient' => true,
			'token'                          => true,
			'subscription_status_code'       => 404,
		],
		'expected' => [
			'can_manage_options'       => true,
			// handle_manual_retry_pro_detection() unconditionally clears the transient and
			// schedules a fresh detection job, regardless of the eventual API answer.
			'failed_transient_cleared' => true,
			'job_scheduled'            => true,
		],
	],
];
