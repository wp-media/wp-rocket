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

	'testShouldClearFailedTransientAndScheduleRetryWhenCurrentUserCan'      => [
		'config'   => [
			'user_role'                       => 'administrator',
			'nonce'                           => 'valid',
			'pro_detection_failed_transient'  => true,
		],
		'expected' => [
			'can_manage_options'       => true,
			'failed_transient_cleared' => true,
			'scheduled_next_attempt'   => 1,
		],
	],
];
