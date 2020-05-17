<?php

return [
	'testShouldReturnErrorPacketWhenNoSubscriptionId' => [
		'rocketcdn_status'     => [ 'subscription_status' => 'cancelled' ],
		'rocketcdn_user_token' => [],
		'expected'             => [
			'status'  => 'error',
			'message' => 'RocketCDN cache purge failed: Missing identifier parameter.',
		],
	],

	'testShouldReturnErrorPacketWhenSubscriptionIdIsZero' => [
		'rocketcdn_status'     => [ 'id' => 0, 'subscription_status' => 'cancelled' ],
		'rocketcdn_user_token' => [],
		'expected'             => [
			'status'  => 'error',
			'message' => 'RocketCDN cache purge failed: Missing identifier parameter.',
		],
	],

	'testShouldReturnErrorPacketWhenNoToken' => [
		'rocketcdn_status'     => [ 'id' => 1 ],
		'rocketcdn_user_token' => [],
		'expected'             => [
			'status'  => 'error',
			'message' => 'RocketCDN cache purge failed: Missing user token.',
		],
	],

	'testShouldReturnErrorPacketWhenInvalidSubscriptionIdOrToken' => [
		'rocketcdn_status'     => [ 'id' => 1 ],
		'rocketcdn_user_token' => '9944b09199c62bcf9418ad846dd0e4bbdfc6ee4b',
		'expected'             => [
			'status'  => 'error',
			'message' => 'RocketCDN cache purge failed: The API returned an unexpected response code.',
		],
	],

	'testShouldReturnSuccessPacketWhenAPIPurgedCache' => [
		'rocketcdn_status'     => [ 'id' => 'ROCKETCDN_WEBSITE_ID' ], // auto-populated.
		'rocketcdn_user_token' => 'ROCKETCDN_TOKEN', // auto-populated.
		'expected'             => [
			'status'  => 'success',
			'message' => 'RocketCDN cache purge successful.',
		],
		'success'              => true,
	],
];
