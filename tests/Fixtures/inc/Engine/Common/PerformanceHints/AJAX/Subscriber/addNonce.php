<?php

return [
	'testShouldReturnFreshNonceWhenLoggedOut' => [
		'config'   => [
			'logged_in' => false,
			'nonce'     => 'fresh_nonce',
		],
		'expected' => [
			'success' => true,
		],
	],
	'testShouldReturnFreshNonceWhenLoggedIn'  => [
		'config'   => [
			'logged_in' => true,
			'nonce'     => 'fresh_nonce',
		],
		'expected' => [
			'success' => true,
		],
	],
];
