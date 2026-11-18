<?php

return [
	'shouldTrackInstallWhenImagifyNotInstalled' => [
		'config'   => [
			'nonce_valid'       => true,
			'can_manage'        => true,
			'imagify_installed' => false,
		],
		'expected' => [
			'tracked' => true,
			'button'  => 'imagify banner install',
		],
	],

	'shouldTrackActivateWhenImagifyInstalled' => [
		'config'   => [
			'nonce_valid'       => true,
			'can_manage'        => true,
			'imagify_installed' => true,
		],
		'expected' => [
			'tracked' => true,
			'button'  => 'imagify banner activate',
		],
	],

	'shouldNotTrackWhenNonceIsInvalid' => [
		'config'   => [
			'nonce_valid'       => false,
			'can_manage'        => true,
			'imagify_installed' => false,
		],
		'expected' => [
			'tracked' => false,
			'button'  => '',
		],
	],

	'shouldNotTrackWhenUserCannotManageOptions' => [
		'config'   => [
			'nonce_valid'       => true,
			'can_manage'        => false,
			'imagify_installed' => false,
		],
		'expected' => [
			'tracked' => false,
			'button'  => '',
		],
	],
];
