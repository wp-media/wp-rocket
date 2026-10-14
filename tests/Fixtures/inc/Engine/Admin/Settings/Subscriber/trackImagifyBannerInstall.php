<?php

return [
	'shouldTrackWhenNonceAndCapabilityAreValid' => [
		'config'   => [
			'nonce_valid' => true,
			'can_manage'  => true,
		],
		'expected' => [
			'tracked' => true,
		],
	],

	'shouldNotTrackWhenNonceIsInvalid' => [
		'config'   => [
			'nonce_valid' => false,
			'can_manage'  => true,
		],
		'expected' => [
			'tracked' => false,
		],
	],

	'shouldNotTrackWhenUserCannotManageOptions' => [
		'config'   => [
			'nonce_valid' => true,
			'can_manage'  => false,
		],
		'expected' => [
			'tracked' => false,
		],
	],
];
