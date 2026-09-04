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

	'testShouldClearFailedTransientAndRunSyncCheckWhenConclusive'               => [
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
			'job_scheduled'            => false,
		],
	],

	'testShouldClearFailedTransientAndRunSyncCheckWhenInconclusive'             => [
		'config'   => [
			'user_role'                      => 'administrator',
			'nonce'                          => 'valid',
			'pro_detection_failed_transient' => true,
			'token'                          => true,
			'subscription_status_code'       => 404,
		],
		'expected' => [
			'can_manage_options'       => true,
			// handle_manual_retry_pro_detection() unconditionally clears the transient and never
			// re-schedules a job — it's a one-shot sync check, not part of the retry/backoff chain.
			'failed_transient_cleared' => true,
			'job_scheduled'            => false,
		],
	],
];
